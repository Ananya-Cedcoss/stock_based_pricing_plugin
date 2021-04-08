(function( $ ) {
    'use strict';
  
  
  // it manages the onchange event for manage stock checkbox.
  $(document).on('change', '#_manage_stock', function () {
    debugger;
    if($('#_manage_stock').prop('checked')==true)
  {
    $($('#_checkbox_for_stock_price').parent()).show();
  
    $('#my_stock_div').show();
  
  }
  if($('#_checkbox_for_stock_price').prop('checked')==false)
  {
    $('#my_stock_div').hide();
  }
  
  }); 
  

  
  // it manages the onchange event for Checkbox used to enable stock Price TextBox.
  $(document).on('change', '#_checkbox_for_stock_price', function () {
    debugger;
    if($('#_checkbox_for_stock_price').prop('checked')==true)
  {
      var quantity=parseInt($('#_stock').val());
   
    if(quantity==0){
  
        alert('Fill Stock Quantity');
        $('#_stock').focus();
        $('#_checkbox_for_stock_price').prop('checked',false)
        return false;
    }
    $('#my_stock_div').show();
  
  }
  
  
  
  
  
  
  }); 
  
  
  
  })( jQuery );
  
  
  $=jQuery;    
  // Generate New Row for Simple Product After validation.
    function GenerateNewRow(){
  
      var index_tr= $('#Stock_table tr').length;  
      for (let index = 1; index < index_tr; index++) {
         if($('#Min_Quantity_'+index).val()=="" || $('#Min_Quantity_'+index).val()==undefined )
        {
             alert("Enter the Min Quantity");
             $('#Min_Quantity_'+index).focus();
            return false;
         }
  
        else if($('#Max_Quantity_'+index).val()=="" || $('#Max_Quantity_'+index).val()==undefined){
           alert("Enter the Max Quantity");
           $('#Max_Quantity_'+index).focus();
            return false;
          }
  
         else if($('#Amount_'+index).val()=="" || $('#Amount_'+index).val()==undefined){
            alert("Enter the Amount");
            $('#Amount_'+index).focus();
            return false;
          }
       }
  
       var innerhtml="";
  
        innerhtml+="<tr ><td> <input type='text' onkeypress='return AllowOnlyNumbers(event);' name='Min[]' style='width:92%' id='Min_Quantity_"+index_tr+"'/>  </td>";
        innerhtml+=" <td> <input type='text'  name='Max[]' onkeypress='return AllowOnlyNumbers(event);' onblur='validateMaxamount(this,"+index_tr+",0)' id='Max_Quantity_"+index_tr+"' style='width:92%'/>  </td>";
        innerhtml+=" <td> <input type='text'  name='Amount[]' onkeypress='return AllowOnlyNumbers(event);' id='Amount_"+index_tr+"' style='width:92%'/>  </td></tr>"; 
        $('#Stock_table').append(innerhtml);
  }
  
  
    // Generate New Row for Variable Product After validation.
  function GenerateNewRow_Variation(id,variation){
  
      var a="#Stock_table_variation_"+(id+1);
      var loop_index=id+1;
      var index_tr= $(a +' tr').length;
      var type="variable";
      for (let index = 1; index < index_tr; index++) {
         if($('#Min_Quantity_Var_'+loop_index+index).val()=="" || $('#Min_Quantity_Var_'+loop_index+index).val()==undefined )
         {
             alert("Enter the Min Quantity");
             $('#Min_Quantity_Var_'+loop_index+index).focus();
            return false;  
         }
  
         else if($('#Max_Quantity_Var_'+loop_index+index).val()=="" || $('#Max_Quantity_Var_'+loop_index+index).val()==undefined){
            alert("Enter the Max Quantity");
            $('#Max_Quantity_Var_'+loop_index+index).focus();
            return false;
    
         }
         else if($('#Amount_Var_'+loop_index+index).val()=="" || $('#Amount_Var_'+loop_index+index).val()==undefined){
            alert("Enter the Amount");
            $('#Amount_Var_'+loop_index+index).focus();
            return false;  
         }        
       }  
        var innerhtml=""; 
    
        innerhtml+="<tr > ";
  
        innerhtml+="<td> <input type='text' onkeypress='return AllowOnlyNumbers(event);'  name='Min_Var_"+variation+"[]' style='width:70%' id='Min_Quantity_Var_"+loop_index+index_tr+"'/>  </td>";
  
        innerhtml+=" <td> <input type='text' name='Max_Var_"+variation+"[]' onkeypress='return AllowOnlyNumbers(event);' onblur='validateMaxamount(this,"+loop_index+index_tr+",-1)' id='Max_Quantity_Var_"+loop_index+index_tr+"' style='width:70%'/>  </td>";
  
        innerhtml+=" <td> <input type='text' onkeypress='return AllowOnlyNumbers(event);'   name='Amount_Var_"+variation+"[]' id='Amount_Var_"+loop_index+index_tr+"' style='width:70%'/>  </td>";
  
        innerhtml+="</tr>";
  
        $(a).append(innerhtml); 
    }
  
  
  // This Function is used to Validate the Numbers and does ont allow alphabets.
    function AllowOnlyNumbers(e) {
  
      e = (e) ? e : window.event;
      var clipboardData = e.clipboardData ? e.clipboardData : window.clipboardData;
      var key = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
      var str = (e.type && e.type == "paste") ? clipboardData.getData('Text') : String.fromCharCode(key);
      
      return (/^\d+$/.test(str));
    }
  
  
  
  
  
   // Validate The Minimum Amount 
  
   function validateMaxamount(obj,Id,type,var_index){
    debugger;
  
    var Max_Value=$(obj).val();
    var Min_Value="";
    if(type==-1){
       Min_Value=$('#Min_Quantity_Var_'+Id).val();
  
    }
    else{
      Min_Value=$('#Min_Quantity_'+Id).val();
    }
  
    if(parseInt(Max_Value)<parseInt(Min_Value)){
      alert("Enter Value Greater Than Minimum Quantity");
      $(obj).val('');
      return false;
  
    }
    
  
    if(var_index>=0)
    {
    var Stock= $('#variable_stock'+var_index).val();
  
    if(Stock=="0" || Stock==undefined){
  alert("Enter Stock quantity !!");
  $(obj).val('');
  $('#variable_stock'+var_index).focus();
  return false;
    }
      if(parseInt(Stock)<parseInt(Max_Value)){
       alert("Enter Maximum Quantity less than Stock Value");
       $(obj).val('');
      }
  
    }  
  }


 