/*https://blog.csdn.net/WuKongHeBaJie/article/details/107493878*/


function gohome(link){
					window.location.href=link;
}


//获取用户的设置；
function getLanguageSetting(user_setting) {
	
	
	return  parseInt(user_setting['isLanguageEng']);
	
}




function setCookie(cname,cvalue,exdays){
			var d = new Date();
			d.setTime(d.getTime()+(exdays*24*60*60*1000));
			var expires = "expires="+d.toGMTString();
		//	document.cookie =cname + "=";
			document.cookie = cname+"="+cvalue+" ; "+expires + " ; path=/;";
}

 function getCookie(cname) {
			var name = cname + "=";
			var ca = document.cookie.split(';');
			for(var i=0; i<ca.length; i++) {
				var c = ca[i].trim();
				if (c.indexOf(name)==0) { return c.substring(name.length,c.length); }
			}
			return "";
}



//  写入  langstr - 全局 session 变量 ， 这个变量 都可以随时访问， 同时将 该用户的语言环境设置写入对应的表中；

function setLanguage(languageValue,path) {
	
	var intlanguageValue = parseInt(languageValue);
	var langstr='en';
	
	var params = new URLSearchParams();
	params.append('languageValue',intlanguageValue); //你要传给后台的参数值 key/value

    // alert('set language method received value:' + languageValue);
    
	if(intlanguageValue ==1) { //表示为英文环境
		langstr='en';
	}else{
		
		langstr='zh-cn';
	}
	//alert(langstr);
	
	axios.post(path + 'member1/update_language_type?lang='+ langstr,params)
	  .then(function (response) {
	   console.log('return result after update:' + response.data);
	  
	 })
	  .catch(function (error) {
		 console.log(error);
	  });
	 // cookie 设置 为 选择语言，如果 未登陆也会有记录。
	//  setCookie('lang',langstr,3650);
	  
	 
}







function 	setLanguageContext(languageValue) {

					if(languageValue ==0){

						return 'En';//如果是中文环境，切换语言的地方提示为 EN
					}else{

						return '中文';
					}


					// console.log(currentLanguage.toUpperCase());
				}// 设置语言环境
				

//设置底部导航菜单 
function changeMenuBottom(index,path){ 
					
				 	switch(index){
				 	case '1':
					 window.location.href=path
				 		break;
				 	case '2':
                           window.location.href=path +"supplier/319188"
				 		break;
				 	case '3':
				 	    window.location.href=path + "member/myorders"
				 		break;
				 	case '4':
				 	    window.location.href=path + "member/showcart1"
				 		break;
				 	default:
				 		window.location.href=path + "member/index"
				 		break;
				 	}
				
				
}				



