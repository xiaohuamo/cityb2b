update cc_user_factory set xero_contact_id='', xero_name ='' where factory_id=319188
update `cc_order` set xero_id ='',xero_invoice_id='' ,sent_to_xero=0  WHERE  business_userId =319188
update   `cc_restaurant_menu`  set xero_itemcode ='' WHERE `restaurant_id` =319188
update   `cc_restaurant_menu_option`  set xero_itemcode ='' 