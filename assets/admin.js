$ = jQuery ;

$(document).ready(function(){
    // $('.gstat_pagination li').click(function(){
    //     $('#gstat_page').val($(this).data('page')).change();
    //     $('.gstat-filter').submit();
    // })
    $('.gstat_next').click(function(){
        $('#gstat_page').val($(this).data('page')).change();
        $('.gstat-filter').submit();
    })
    $('.gstat_prev').click(function(){
        $('#gstat_page').val($(this).data('page')).change();
        $('.gstat-filter').submit();
    })
})