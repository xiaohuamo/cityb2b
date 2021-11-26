 <?php
class mdl_business_list extends mdl_base
{
	

	
	
	
	
}

  //根据登陆用户的身份，并根据业务需要获得商家列表
  
	class getBusinessListCentre {
		
		// 获得某个登陆用户授权管理的客户列表；
		public static function getAuthoriseBusinessList($currentLoginUser) {
		
			 $mdl_authrise_manage_other_business_account = loadModel('authrise_manage_other_business_account');
			 $sql = "select a.*,b.displayName  from cc_authrise_manage_other_business_account a left join cc_user b on a.customer_id =b.id  where authorise_business_id =$currentLoginUser";
			 $authrise_manage_other_business_account =$mdl_authrise_manage_other_business_account->getListBySql($sql);
		     return $authrise_manage_other_business_account;
		}
		
		public static function getDispatchingCentreBusinessList($currentLoginUser) {
		
			 
		}
		
		
	
	}