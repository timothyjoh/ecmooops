jQuery( document ).ready( function() {
  deactivateCheckbox = '<tr><td class="label"><label for="propel-deactivate">Deactivate Keys:</label></td><td class="total"><input type="checkbox" id="propel-deactivate" name="propel-deactivate" checked="checked"></td></tr>';
  // jQuery( '.wc-order-refund-items table tbody' ).prepend(deactivateCheckbox)
  jQuery('label[for=restock_refunded_items]').html("Deactivate Keys?")
});