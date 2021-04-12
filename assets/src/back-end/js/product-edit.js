(function( $ ) {
  'use strict';  

// it manages the onchange event for manage stock checkbox.
$(document).on('change', '#_manage_stock', function () {
  debugger;
  if ( $('#_manage_stock').prop('checked') == true ) {
   $($('#_checkbox_for_stock_price').parent()).show();  
   $('#my_stock_div').show();  
    } else {
      $('#my_stock_div').hide();
    } 
  });
})( jQuery ); 


// Generate New Row for Simple Product After validation.
  function GenerateNewRow(){  
    var index_tr= jQuery('#Stock_table tr').length;  
    var deleted_row=0;
    for (let index = 1; index < index_tr; index++) {
    if ( jQuery('#Min_Quantity_'+index).length>0 ) {
       if ( jQuery('#Min_Quantity_'+index).val() == "" || jQuery('#Min_Quantity_'+index).val() == undefined ) {     
            alert(sbpp_productedit_param.min_quantity);
            jQuery('#Min_Quantity_'+index).focus();
            return false;
         } else if ( jQuery('#Max_Quantity_'+index).val() == "" || jQuery('#Max_Quantity_'+index).val() == undefined ) {
                alert(sbpp_productedit_param.max_quantity);
                jQuery('#Max_Quantity_'+index).focus();
                 return false;
             } else if ( jQuery('#Amount_'+index).val() == "" || jQuery('#Amount_'+index).val() == undefined ) {
               alert(sbpp_productedit_param.amount);
               jQuery('#Amount_'+index).focus();
               return false;
            }
        } else {
        deleted_row=index;
      }
     } 
     if(parseInt(deleted_row)>0 ){
      index_tr=deleted_row;
     } 
     var innerhtml="";  
      innerhtml+="<tr ><td> <input type='text' onkeypress='return AllowOnlyNumbers(event);' name='Min[]'  id='Min_Quantity_"+index_tr+"'/>  </td>";
      innerhtml+=" <td> <input type='text'  name='Max[]' onkeypress='return AllowOnlyNumbers(event);' onblur='validateMaxamount(this,"+index_tr+",0)' id='Max_Quantity_"+index_tr+"' />  </td>";
      innerhtml+=" <td> <input type='text'  name='Amount[]' onkeypress='return AllowOnlyNumbers(event);' id='Amount_"+index_tr+"'/>  </td>"; 
      innerhtml+=" <td> <span  onclick='GenerateNewRow()'> <b>+</b></span> &nbsp; <span class='delete_row' onclick='DeleteExistingRow(this)'><b>-</b></span> </td> </tr> ";
      jQuery('#Stock_table').append(innerhtml);
}

  // Generate New Row for Variable Product After validation.
function GenerateNewRow_Variation(id,variation){
    var a="#Stock_table_variation_"+(parseInt(id)+1);
    var loop_index=parseInt(id)+1;
    var deleted_row=0;
    var index_tr= jQuery(a +' tr').length;
    var type="variable";
    var add_row_dat=jQuery(jQuery(a+' tr td')[3]).html();
    for (let index = 1; index < index_tr; index++) {
      if(jQuery('#Min_Quantity_Var_'+loop_index+index).length>0){
       if(jQuery('#Min_Quantity_Var_'+loop_index+index).val()=="" || jQuery('#Min_Quantity_Var_'+loop_index+index).val()==undefined )
       {
        alert(sbpp_productedit_param.min_quantity);
           jQuery('#Min_Quantity_Var_'+loop_index+index).focus();
          return false;  
       }  
       else if(jQuery('#Max_Quantity_Var_'+loop_index+index).val()=="" || jQuery('#Max_Quantity_Var_'+loop_index+index).val()==undefined){
        alert(sbpp_productedit_param.max_quantity);
          jQuery('#Max_Quantity_Var_'+loop_index+index).focus();
          return false;    
       }
       else if(jQuery('#Amount_Var_'+loop_index+index).val()=="" || jQuery('#Amount_Var_'+loop_index+index).val()==undefined){
        alert(sbpp_productedit_param.amount);
          jQuery('#Amount_Var_'+loop_index+index).focus();
          return false;  
       }    
      } 
      else{
        deleted_row=index;
      }   
     }  
     if(parseInt(deleted_row)>0 ){
      index_tr=deleted_row;
     }
      var innerhtml="";     
      innerhtml+="<tr > ";  
      innerhtml+="<td> <input type='text' onkeypress='return AllowOnlyNumbers(event);'  name='Min_Var_"+variation+"[]'  id='Min_Quantity_Var_"+loop_index+index_tr+"'/>  </td>";  
      innerhtml+=" <td> <input type='text' name='Max_Var_"+variation+"[]' onkeypress='return AllowOnlyNumbers(event);' onblur='validateMaxamount(this,"+loop_index+index_tr+",-1)' id='Max_Quantity_Var_"+loop_index+index_tr+"' />  </td>";  
      innerhtml+=" <td> <input type='text' onkeypress='return AllowOnlyNumbers(event);'   name='Amount_Var_"+variation+"[]' id='Amount_Var_"+loop_index+index_tr+"'/>  </td>";  
      innerhtml+=" <td>  "+add_row_dat+" &nbsp;    <span class='delete_row left' onclick='DeleteExistingRow(this)'><u> <b>-</b> </u></span><span class='hide_span'> </span> &nbsp;&nbsp;&nbsp;&nbsp;</td> </tr> ";   
      jQuery(a).append(innerhtml); 
  }

  

// This Function is used to Validate the Numbers and does ont allow alphabets.
function AllowOnlyNumbers(evt) {
  evt = (evt) ? evt : window.event;
  var charCode = (evt.which) ? evt.which : evt.keyCode;
  if (charCode > 31 && (charCode < 48 || charCode > 57)) {
      return false;
  }
  return true;
  }   
  
 // Validate The Minimum Amount   
 function validateMaxamount(obj,Id,type,var_index){
  debugger;  
  var Max_Value=jQuery(obj).val();
  var Min_Value="";
  if (type==-1){
     Min_Value=jQuery('#Min_Quantity_Var_'+Id).val();  
  }
  else{
    Min_Value=jQuery('#Min_Quantity_'+Id).val();
  }  
  if (parseInt(Max_Value)<parseInt(Min_Value)){
    alert(sbpp_productedit_param.greater_than_minimum);
    jQuery(obj).val('');
    return false;  
  }   
 
}
// Delete The Row !!!   
function DeleteExistingRow(obj)
{
jQuery(jQuery(obj).parent()).parent().remove();
}