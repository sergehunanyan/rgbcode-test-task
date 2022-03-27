(function($) {
    $(document).ready(function(){

        $(document).on('change', '#rgb-users-table-role', function () {
            $('.rgb-users-table-page').val(1)
            usersTableGenerate()
        });

        $(document).on('click', '.rgb-users-table-change-orderby', function () {
            let type = $(this).attr('data-orderby'),
                current_orderby = $('.rgb-users-table-orderby'),
                current_order = $('.rgb-users-table-order')

            if(type === current_orderby.val()){
                current_order.val(current_order.val() == 'ASC' ? 'DESC' : 'ASC')
            }else{
                current_orderby.val(type)
            }

            usersTableGenerate()
        });

        // Pagination
        function usersTableGenerate(){
            let fd = new FormData($('#rgb-users-table-form')[0]);

            fd.append('action','users_table_generate');
            fd.append('nonce',rgb_users_table.nonce);

            $.ajax( {
                url: rgb_users_table.ajax_url,
                type: 'post',
                data: fd,
                cache: false,
                processData: false,
                contentType: false,
                success( response ) {
                    let data = JSON.parse(response)
                    $('.rgb-users-table tbody').html(data.users);
                    $(".rgb-users-table-pagination").html(data.pagination);
                },
            } );
        }

        $(document).on('click', '.rgb-users-table-pagination li.active',function(){
            let page = $(this).attr('p');
            $('.rgb-users-table-page').val(page)
            usersTableGenerate()
        });

        usersTableGenerate()

    });
}(jQuery));