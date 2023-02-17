$( '#reset_btn' ).on("click", function() {
    $( '#full_url, #short_url' ).val('');
});

$( '#gen_link_btn' ).on("click", function() {
    if(!txvalidator($("#full_url").val(),"TX_URL")){
        $('#full_url').addClass("is-invalid");
        return false;				
    }
});
$('#full_url').on('change keyup', function(e){
    $('#full_url').removeClass("is-invalid");
});

$( '#copy_link_btn' ).on("click", function() {
    clickCopy();
});

function clickCopy() {
    
    /* Get the text field */
    var copyText = document.getElementById("short_url");
    
    if( document.getElementById("short_url").value != '' ){

        /* Select the text field */
        copyText.select();
        copyText.setSelectionRange(0, 99999); /*For mobile devices*/

        /* Copy the text inside the text field */
        document.execCommand("copy");

        /* Alert the copied text */
        alert("Copied the text: " + copyText.value);

    }else{
        
        alert("Nothing to copy.");
        
    }

} 