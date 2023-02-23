{
	#Author - Yu Mon -- Updated - Khin
	#this script will update unsubscribe_list based on Configuration keyword
	
package Plugins::momSurvey;

use strict;
use POSIX qw(strftime);
use POSIX ":sys_wait_h";
use Data::Dumper;
use IO::File;
use Date::Calc qw(:all);
use List::Util 'first'; 
use experimental 'smartmatch';
use XML::Simple;
use utf8;
use POSIX qw/ceil/;
use LWP::UserAgent;
use JSON;
use MIME::Base64;
use Encode qw(encode_utf8);
use Data::Dumper;

#use strict;
#use LWP::UserAgent;
use URI::Escape;
#use Date::Calc qw(Today_and_Now());
#use Date::Calc qw(:all);
#use POSIX qw(strftime);
#use XML::Simple;

use lib "/home/msg/lib";
use dbi;
use dbconf;
use miscUtil;
use Spooler;
use APP_ctl;

my $app_home = "/home/msg";
my $matchflag = 0;

sub new
{
	my $proto = shift;
	my $logger = shift;

	my $self = {};
	bless $self;

	if( defined($logger) ){
		$self->{LOGGER} = $logger;
	}

	$self->{ME} = $proto;
	$self->{UseSemaphore} = 0;

	$matchflag = 0;

	return $self;
}

sub SetSemaphore
{
	my $self = shift;
	my $semaphore = shift;

	$self->{UseSemaphore} = 1;
	$self->{Semaphore} = $semaphore;

	return;
}

sub msglog
{
	my $self = shift;
	my $msg = shift;
	my $proto = $self->{ME};

	my $buffer = "(" . $proto . ") " . $msg;

	if( defined($self->{LOGGER}) ){
		$self->{LOGGER}->msglog($buffer);
	} else {
		warn $buffer;
	}

	return;
}

sub Matchflag
{
	my $self = shift;

	return $matchflag;
}

sub runner
{
	my $self = shift;
	my $smsinfo = shift;

	my $msgid = $smsinfo->{msgid};
	my $mno = $smsinfo->{mno};
	my $sms_string = $smsinfo->{sms_string};
	my $dtm_string = $smsinfo->{dtm};
	my $smsc = $smsinfo->{smsc};
	my $imei = $smsinfo->{imei};
	
	if($self->CheckWebappMode() == 0){
		$self->msglog("Webapp is disabled. Ignore.\n");
		return;
	}else{
		
		my $spldbcf = new dbconf("/home/msg/project");
		my $spldblink = $spldbcf->{CF}->{spdb}->{dblink};
		my $spldbuser = $spldbcf->{CF}->{spdb}->{dbuser};
		my $spldbpass = $spldbcf->{CF}->{spdb}->{dbpass};
		my $spldb = new dbi($spldblink, $spldbuser, $spldbpass);
		$spldb->OpenConnection();
		
		my $dbcf = new dbconf("/home/msg/project");
		my $dblink = $dbcf->{CF}->{momdb}->{dblink};
		my $dbuser = $dbcf->{CF}->{momdb}->{dbuser};
		my $dbpass = $dbcf->{CF}->{momdb}->{dbpass};
		my $db = new dbi($dblink, $dbuser, $dbpass);
		$db->OpenConnection();
		
		#survey( $db, $spldb, $user_id, $msg );
		if( $self->survey( $db, $spldb, $mno, $sms_string ) == 1 ){
			$matchflag++;
		}
	
	}
	
	return $matchflag;
}

sub survey{
	
	my ( $self, $db, $spldb, $user_id, $msg ) = @_;
	
	my @msg_array = split(' ', $msg); 
	my $keyword_received = $msg_array[0];
	my $full_msg_received = $msg;
	
	$self->msglog("starting survey.....\n");
	
	my $xml = new XML::Simple;
	my $xml_data = $xml->XMLin( '/home/msg/conf/app_config.xml' );
	my $max_sms = $xml_data->{sms_per_email};
	my $long_sms = $xml_data->{long_sms};
	
	$user_id = "+".$user_id unless $user_id =~ /^\+/;
	
	my $fields1 = "a.*, b.keyword";
	my $tbname1 = "campagin_survey_outbox a, campaign_mgnt b";
	my $cond1 = "a.campagin_id = b.campaign_id and a.mobile_no = '$user_id' and b.campaign_start_date <= 'now()' and b.campaign_end_date >= 'now()' and b.campaign_type = '2' and b.campaign_status = 'active'";
	my $orderby1 = "NA";
	
	#$db->OpenConnection();
	$db->Select($fields1,$tbname1,$cond1,$orderby1,'0','0');
	my $dbres1 = $db->{DBRES};
	
	#$self->msglog( "total campagin record >>" . scalar(@{$dbres1}) . "\n\n" );
	
	if(scalar(@{$dbres1}) > 0){
		
		foreach my $arr1(@{$dbres1}){
			
			my $id = $arr1->[0];#a.*, from campagin_survey_outbox table
			my $campagin_id = $arr1->[1];#a.*, from campagin_survey_outbox table
			my $department = $arr1->[2];#a.*, from campagin_survey_outbox table
			my $keywords = $arr1->[3];#a.*, from campagin_survey_outbox table
			my $type = $arr1->[4];#a.*, from campagin_survey_outbox table
			my $send_mode = $arr1->[5];#a.*, from campagin_survey_outbox table
			my $mobile_no = $arr1->[6];#a.*, from campagin_survey_outbox table
			my $bot_id = $arr1->[7];#a.*, from campagin_survey_outbox table
			my $cdtm = $arr1->[8];#a.*, from campagin_survey_outbox table
			my $cby = $arr1->[9];#a.*, from campagin_survey_outbox table
			my $label = $arr1->[10];#a.*, from campagin_survey_outbox table
			my $keyword2 = $arr1->[11];#a.*, from campagin_survey_outbox table
			my $keyword = $arr1->[12];#b.keyword from campaign_mgnt table
		
			if( $keywords ){
				
				#get keyword list to compare
				my @keyword_list = (  ); 
				my $arr2;
				my $fields2 = "keyword";
				my $tbname2 = "mom_sms_response";
				my $cond2 = "id in ( $keywords )";
				my $orderby2 = "NA";
				#$db->OpenConnection();
				$db->Select($fields2,$tbname2,$cond2,$orderby2,'0','0');
				my $dbres2 = $db->{DBRES};
				if(scalar(@{$dbres2}) > 0){
					
					foreach $arr2(@{$dbres2}){
						push(@keyword_list, ( $arr2->[0] )); 
						#$self->msglog( "keyword from db >>" . $arr2->[0] . "\n\n");
					}
					
				}
				#end get keyword list to compare
				if ( grep( /^$keyword_received$/i, @keyword_list ) ) {
					
					#keyword matched
					
					#check only can insert one time for each mobile and survey
					my $total_exited = 0;
					
					my $arr3;
					my $fields3 = "count(*) as total";
					my $tbname3 = "campagin_survey_inbox";
					my $cond3 = "mobile_no = '$user_id' and campagin_id = '$campagin_id'";
					my $orderby3 = "NA";
					#$db->OpenConnection();
					$db->Select($fields3,$tbname3,$cond3,$orderby3,'0','0');
					my $dbres3 = $db->{DBRES};
					if(scalar(@{$dbres3}) > 0){
						
						foreach $arr3(@{$dbres3}){
							$total_exited = $arr3->[0];
						}
						
					}
					#end check
					#print "total_exited: " . $total_exited . "\n";
				
					if( $total_exited eq 0 ){
						
						my ( $msg_length_status, $total_sms, $msg_type ) = $self->get_total_sms( $full_msg_received, $max_sms, $long_sms, 'no' );
						if( $total_sms <= 0 ){
							$total_sms = 1;
						}else{
							$total_sms = ceil( $total_sms );
						}
						
						#continue
						my $sql1 = "insert into campagin_survey_inbox ( campagin_id, department, keywords, type, send_mode, bot_id, mobile_no, keyword_received, full_msg_received, received_via, total_sms ) values ( '$campagin_id', '$department', '$keywords', '$type', '$send_mode', '$bot_id', '$user_id', '$keyword_received', '$full_msg_received', 'sms', '$total_sms' )";
						
						#$self->msglog(" $sql1.....\n");
						
						my $affected_row1 = $db->do_sqlcmd($sql1);
						if( $affected_row1 > 0 ){
							
							#get auto reply
							my $arr4;
							my $autoreply;
							my $autoreply_msg;
							
							my $lc_keyword_received = lc $keyword_received;
							my $fields4 = "autoreply, autoreply_msg";
							my $tbname4 = "mom_sms_response";
							my $cond4 = "lower(keyword) = '$lc_keyword_received'";
							my $orderby4 = "NA";
							
							#$db->OpenConnection();
							$db->Select($fields4,$tbname4,$cond4,$orderby4,'0','0');
							my $dbres4 = $db->{DBRES};
							if(scalar(@{$dbres4}) > 0){
								
								foreach $arr4(@{$dbres4}){
									$autoreply = $arr4->[0];
									$autoreply_msg = $arr4->[1];
								}
								
							}
							#end get
							
							#$self->msglog( "autoreply: $autoreply | autoreply_msg: $autoreply_msg | reply_inbox: $total_exited \n\n" );
							
							if( $autoreply eq "1" && $autoreply_msg && $total_exited eq 0 ){#each mobile only can reply one time
								
								#this script is for sms, sms is a must reply regardless send_mode is sms or sms_mim
								
								#if( $send_mode == "sms_mim" ){
									
									#no need send mim, this script is for normal sms reply
								#}else{
									
									my( $year, $month, $day, $hour, $min, $sec ) = Today_and_Now();
									
									my $xml = new XML::Simple;
									my $xml_data = $xml->XMLin( '/home/msg/conf/clusterconfig.xml' );
									my $system_mode = $xml_data->{system_mode};
									my $prefix = $xml_data->{system_mode};
									my $server_prefix = getServerPrefix( $system_mode, $prefix );
									
									my $t = $db->getSequence('message_trackid');
									my $trackid = $server_prefix.$hour.$min.$sec.$t;				
									my $outgoing_id = $server_prefix . "X" . $year . $day . $hour . $min . sprintf( "%06d", $db->getSequence('outgoing_logs_outgoing_id_seq') );
							
									my $mode = "text";
									my $priority = "5";
									my $msg_from = $label;
									
									my $status = $self->sendSMSASP($user_id, $autoreply_msg, $trackid, $label, $spldb );
							
									my $sql1 = "insert into outgoing_logs (outgoing_id,priority,trackid,sent_by,department,mobile_numb,message,message_status,modem_label, campaign_id, completed_dtm, mim_tpl_id ) values ('".$outgoing_id."','".$priority."','".$trackid."','".$msg_from."','".$department."','".$user_id."', '".$autoreply_msg."', '".$status."','".$label."', '".$campagin_id."', now(), '')";								
									#$self->msglog("sql1: $sql1 \n\n");
									
									my $affected_row = $db->do_sqlcmd($sql1);
									if( $affected_row > 0 ){
										#$self->msglog("inserted into outgoing_logs \n\n");
									}else{
										
										#$self->msglog("failed to insert into outgoing_logs \n\n");
									}
									
									#$self->msglog("survey keyword reply sent to $user_id \n\n");
									
									#my $sql2 = "INSERT INTO webapp_sms (msgid, mobile_numb, msg_content, mode, priority, msg_from, msg_status, label, campaign_id ) VALUES ('$trackid', '$user_id', '$autoreply_msg', '$mode', '$priority', '$msg_from', 'W','$label', '$campagin_id') ";
									#my $affected_row2 = $db->do_sqlcmd($sql2);
									#if( $affected_row2 > 0 ){
										#print "inserted into webapp_sms \n\n";
									#}else{
										#print "failed to insert into webapp_sms \n\n";
									#}
									
								#}
								
							}
							
						}
				
						
					}else{
						
						#ignore
					}
					
					
					
					
				}else{
					
					#keyword not matched
					#$self->msglog("survey keyword not match.....\n");
					
				}
				
				#print "keyword_list Array: @keyword_list \n\n"; 
				
			}
		
		}
		
	}
	
	#print "user_id: $user_id | msg: $msg \n\n";
	return 1;
	
}

sub getServerPrefix{
	
	my ( $system_server_mode, $prefix ) = @_;
	
	if($system_server_mode == '2'){ #primary server
		return "P";
	}elsif($system_server_mode == '3'){ #secondary server
		return "S";
	}elsif($system_server_mode == '4' || $system_server_mode == '5'){ #data sync mode
		return $prefix;
	}else{
		return "C";
	}
	
}

sub MatchingUnsub
{
	my $self = shift;
	my $msgid = shift;
	my $sms_string = shift;
	my $mno = shift;
	
	my ($keyword,@nouse) = split(/\s/, $sms_string);
	
	my $flag = 0;
	
	my $dbcf = new dbconf($app_home);

	my $dblink = $dbcf->{CF}->{webappdb}->{dblink};
	my $dbuser = $dbcf->{CF}->{webappdb}->{dbuser};
	my $dbpass = $dbcf->{CF}->{webappdb}->{dbpass};

	my $db = new dbi($dblink, $dbuser, $dbpass);

	$db->OpenConnection();

	if( $db->CheckStatus() == 0 ){
		$self->msglog("DB error: " . $db->getMessage());
		return;
	}

	if( $self->{UseSemaphore} == 1 ){
		$db->SetSemaphore($self->{Semaphore});
	}
	
	my $tbname = "unsub_keyword";
	my $field = "keyword";
	my $condition = "NA";
	my $orderby = "NA";
	my $offset = "0";
	my $limit = "0";
	
	$db->Select($field, $tbname, $condition, $orderby, $offset, $limit);
	my $res = $db->{DBRES};
	
	foreach my $elem(@{$res}){
		my $unsub = $elem->[0];
		if( lc($keyword) eq lc($unsub) ){
			$self->updateUnsub($mno,$sms_string);
			$flag = 1;
			return $flag;
		}
	}
	
	return $flag;
}

sub CheckWebappMode
{
	my $self = shift;
	
	my $ret = `cat /home/msg/conf/webapp_mode`;
	$ret =~ s/^\s+//;
	$ret =~ s/\s+$//;
	if($ret eq '1' || $ret eq  'Y' || $ret eq 'y'){
		return 1;
	}
	return 0;
}

sub updateUnsub
{
	my $self = shift;
	my $mno = shift;
	my $sms_string = shift;
	
	my $dbcf = new dbconf($app_home);

	my $dblink = $dbcf->{CF}->{webappdb}->{dblink};
	my $dbuser = $dbcf->{CF}->{webappdb}->{dbuser};
	my $dbpass = $dbcf->{CF}->{webappdb}->{dbpass};

	my $db = new dbi($dblink, $dbuser, $dbpass);

	$db->OpenConnection();

	if( $db->CheckStatus() == 0 ){
		$self->msglog("DB error: " . $db->getMessage());
		return;
	}
	
	if( $self->{UseSemaphore} == 1 ){
		$db->SetSemaphore($self->{Semaphore});
	}
	
	if (defined($mno) && length($mno) > 0)
	{
		my $result = $self->sendAutoResponse($mno, $db);

		my $checkMno = $self->checkMobile($mno,$db);
		if( $checkMno eq "1" ){
			return;
		}
		
		my $misc = new miscUtil();
		$sms_string = $misc->dbSafe($sms_string);
		$mno = $misc->dbSafe($mno);
	
		my $sqlcmd = "insert into unsubscribe_list " . 
		"(mobile_numb, created_dtm, incoming_msg) " .
		"values " .
		"('$mno', 'now()', '$sms_string') ";
		$db->do_sqlcmd($sqlcmd);

		if( $db->CheckStatus() == 0 ){
			$self->msglog("SQL Error: $sqlcmd <$@>");
		}
	   
		$db->CloseConnection();
	}

	return;
}

sub sendAutoResponse
{
	my $self = shift;
	my $mno = shift;
	my $db = shift;
	
	my $tbname = "unsub_response";
	my $field = "response";
	my $condition = "NA";
	my $orderby = "NA";
	my $offset = "0";
	my $limit = "0";
	
	$db->Select($field, $tbname, $condition, $orderby, $offset, $limit);
	my $res = $db->{DBRES};
	
	my $response = $res->[0][0];

	if( !defined($response) ){
		return;
	}

	if( length($response) == 0 || $response =~ /^\s+$/ ){
		return;
	}
	
	my $result = $self->postURL($mno, $response);
	
	return $result;
}

sub postURL
{
	my $self = shift;
	my $tar_num = shift;
	my $tar_msg= shift;
	my $tar_priority = '5';

	my $appctl = new APP_ctl();
	my $misc = new miscUtil();
	my $dbcf = new dbconf("/home/msg");
	my $db = new dbi($dbcf->{CF}->{spdb}->{dblink},
		$dbcf->{CF}->{spdb}->{dbuser},
		$dbcf->{CF}->{spdb}->{dbpass});

	$db->OpenConnection();

	if( $self->{UseSemaphore} == 1 ){
		$db->SetSemaphore($self->{Semaphore});
	}
	
	my $total_sms = $appctl->Get_TotalSMSPerMail();
	my $long_sms = $appctl->Get_LongSMSMode();

	my $charset = 1;

	if( $misc->checkASCII($tar_msg) == 1 ){
		$charset = 1;
	} else {
		$charset = 2;
	}

	my $spooler = new Spooler({DBI=>$db, LOGGER=>$self->{LOGGER},
		LONG_SMS=>$long_sms, TOTAL_SMS=>$total_sms,
		CHARSET=>$charset, PRIORITY=>$tar_priority,
		CASE_ID=>0, SENDER=>"unsub_reply",
		MSG_TYPE=>'A'});

	if( $self->{UseSemaphore} == 1 ){
		$spooler->SetSemaphore($self->{Semaphore});
	}

	my $res = "";
	if( $charset == 1 ){
		$res = $spooler->SpoolMsg($tar_num, $tar_msg);
	} else {
		$res = $spooler->SpoolUnicodeMsg($tar_num, $tar_msg);
	}

	$self->msglog("UNSUB: $tar_num - $res");

	return 1;
}

sub checkMobile
{
	my $self = shift;
	my $mno = shift;
	my $db = shift;
	
	my $tbname = "unsubscribe_list";
	my $field = "mobile_numb";
	my $condition = "NA";
	my $orderby = "NA";
	my $offset = "0";
	my $limit = "0";
	
	$db->Select($field, $tbname, $condition, $orderby, $offset, $limit);
	my $res = $db->{DBRES};
	
	my $misc = new miscUtil();
	
	my $flag = 0;
	
	foreach my $elem(@{$res}){
		my $temp = $elem->[0];
			
		my $counter = $misc->matchNumb($temp,$mno);
		if( $counter eq "1" ){
			$flag = 1;
		}
	}
	
	return $flag;
}

sub get_total_sms{
	
	my ($self,$message, $max_sms, $long_sms, $from_csv ) = @_;
	my $message_len = shift;
	my $tmp_internal = shift;
	my $unicode_string = shift;
	my $unicode_hex = shift;
	my $totalchar = shift;
	
	#my $msg_type = utf8::is_utf8($message);
	my $msg_type = $self->checkASCII($message);#1=yes, 0=unicode
	
	my $max_length = 153;
	
	if( $long_sms == 0 ){
		
		if( $msg_type == 0 ){#unicode
			$max_length = 70;
		}else{
			$max_length = 160;#ascii
		}
		
	}else{
		
		if( $msg_type == 0 ){#unicode
			$max_length = 67;
		}else{
			$max_length = 153;#ascii
		}
		
	}
	
	if( $msg_type == 0 ){
		
		#unicode
		if( $from_csv eq "yes" ){
			
			$tmp_internal = $message;
			$unicode_string = encode("UCS-2BE", $tmp_internal);
			$unicode_hex = unpack("H*", $unicode_string);
			$totalchar = length($unicode_hex) / 4;
			
		}else{
		
			$message =~ s/\x00//g;

			$tmp_internal = decode("utf8", $message);
			$unicode_string = encode("UCS-2BE", $tmp_internal);
			$unicode_hex = unpack("H*", $unicode_string);
			$totalchar = length($unicode_hex) / 4;
			
		}
		
		$message_len = $totalchar;

	}else{
		
		#ascii
		$message_len = length( $message );
	
	}
	
	#my $total_sms = length( $message ) / $max_length;
	my $total_sms = $message_len / $max_length;
	my $status = 'valid';
	
	if( ( $max_length * $max_sms ) <  length( $message ) ){
	#if( $total_sms > $max_sms ){
		$status = 'invalid';
	}else{
		$status = 'valid';
	}
	
	my @returns = ( $status, $total_sms, $msg_type ); 
	
	#print Dumper \@returns;
	
	#exit;
	
	return @returns;
}

sub checkASCII{
	
	my ($message) = @_;
	
	if( defined($message) ){
		
		my @chars = split(//, $message);

		foreach my $i (@chars) {

			if( $i !~ /^[\040-\137]$/ &&
			$i !~ /^[\141-\176]$/ &&
			$i !~ /^[\010-\015]$/ ){
					return 0;
				}
		}
		
	}

	return 1;
}

sub getDirectConn{
		
	my ( $spdb, $label ) = @_;

	my $fields3 = "username, password";
	my $tbname3 = "direct_conn";
	my $cond3 = "label = '$label'";
	my $orderby1 = "NA";
	my $DirectConn = '';
	
	$spdb->Select($fields3,$tbname3,$cond3,$orderby1,'0','0');
	my $dbres3 = $spdb->{DBRES};
	if(scalar(@{$dbres3}) > 0){
		
		foreach my $arr3(@{$dbres3}){
			$DirectConn = $arr3;
		}
		
	}
	
	#my $DirectConn = $spdb->SelectSingleRow( $fields3, $tbname3, $cond3 );
	
	return $DirectConn;
	
}
	
sub sendSMSASP
{
		#my ( $tar_num, $msgstr, $trackid, $label, $spdb ) = @_;
		
		my $self = shift;
		my $tar_num = shift;
		my $msgstr = shift;
		my $trackid = shift;
		my $label = shift;
		my $spdb = shift;
		
		$msgstr =~ s/<br>/\n/g;
		$msgstr =~ s/^\s+//g;
		$msgstr =~ s/\s+$//g;

		my $username = "";
		my $passwd = "";
	
		if( $label ne '' ){
			
			my $DirectConnInfo = getDirectConn( $spdb, $label );
			my ( $username_key, $username_value ) = split /=/, $DirectConnInfo->[0];
			my ( $password_key, $password_value ) = split /=/, $DirectConnInfo->[1];
			
			$username = $username_value;
			$passwd = $password_value;
			
			#$self->msglog("username: $username | passwd: $passwd \n");
			
		}else{
			
			#$self->msglog("failed get direct connection \n");
			
			return "FAIL";
		}
		
		#if($label eq "Demo"){
			#$username = 'dolza';
			#$passwd = '123';
		#}elsif($label eq "MOM" || $label eq "6598369352"){
			#$username = 'momsms';
			#$passwd = 'J9c6sba3cg';
		#}else{
			#return "FAIL";
		#}		

		my $misc = new miscUtil();
		my $action_url = "https://web.sendquickasp.com/client_api/index.php";
		my $params = {
			username => $username,
			passwd=> $passwd,
			tar_num=> $tar_num,
			tar_msg=> $msgstr,
			trackid=> $trackid,
			callerid=> $label,
			route_to=> 'api_send_sms' ,
			status_url=> 'https://mom.sendquickasp.com/mom/delivery_status.php'
		};
		my $results = $misc->URLSubmit($action_url,$params);  
		my $result_content = $results->{content};
		$result_content =~ s/^\s+//g;
		$result_content =~ s/\s+$//g;			
		
		print  "MNO: $tar_num; ID: $trackid; Label: $label, Content: ".$result_content;
		
		if($result_content eq "sent" || $result_content eq "queued"){
			return "Y";
		}else{
			return "F";
		}			
}

}

1;
