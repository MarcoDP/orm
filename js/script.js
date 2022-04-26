$(document).ready(function(){

    $('.col-8 h5').hide();
    
    $('#formDatabase').submit(function(e){
        
        e.preventDefault();

        $('#chkSelectAll').prop('checked', false);
        
        var data = $(this).serialize();
        
        $.ajax({
            type: 'POST',
            url: 'ajax/db_interrogation.php',
            data: data,
            dataType: 'JSON',
            success: function(result) {
               
                if (result.status == true) {

                    $('tbody').html('');

                    $.each(result['data'], function(index, value){
                    
                        var tr = '<tr><td class="text-center"><input name="tables['+index+'][tablename]" type="checkbox" value="'+value+'" class="text-center chkSelectTable"></td><td>'+value+'</td><td><input name="tables['+index+'][classname]" type="text" class="form-control form-control-sm className"></td></tr>';
                        
                        $('tbody').append(tr);

                    });

                    $('#btnInterrogaDatabase').prop("disabled", true);
                    $('#btnGeneraClassi').prop("disabled", false);
                    
                } else if (result.status == false){

                    $('.toast-body').text(result.message);
                    var toastLive = $('#liveToast');
                    var toast = new bootstrap.Toast(toastLive);
                    toast.show();

                }

            }
        });
        
    });
    
    $('#chkSelectAll').change(function(){
        
        $(this).prop('checked') ? $('.chkSelectTable').prop('checked', true) : $('.chkSelectTable').prop('checked', false);
        
    });
    
    $('#btnGeneraClassi').click(function(){
        
        $('.col-8 ul li').remove();

        var db_data = $('#formDatabase').serialize();
        var tables_data = $('.chkSelectTable').serialize();
        var class_name = $('.className').serialize();
        var data = db_data + '&' + tables_data + '&' + class_name;
        
        $.ajax({
            type: 'POST',
            url: 'ajax/classes_creation.php',
            data: data,
            dataType: 'JSON',
            success: function(result) {
            
                if (result.status == true) {
                
                    $('.col-8 h5').show();

                    $.each(result['classe'], function(index, value){

                        console.log(value);

                        var item = '<li>'+value+'</li>';                                           
                        
                        $('.col-8 ul').append(item);

                    });

                    $('.accordion-collapse').collapse('hide');

                } else if (result.status == false){

                    $('.toast-body').text(result.message);
                    var toastLive = $('#liveToast');
                    var toast = new bootstrap.Toast(toastLive);
                    toast.show();

                }

            }
        });
        
    });
    
});