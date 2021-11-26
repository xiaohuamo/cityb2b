(function($){

		$.fn.verifySMS = function(){
			//console.log('init');
            var context = $(this);
		   
			
			var varifiedHtml = " <em class='varifiedHtml' style='display:none'><i class='fa fa-check-circle' style='color: #30af30;'></i>"+hint3+"</em> ";
			var startVarifiedHtml =  " <em class='start-varify startVarifiedHtml' style='cursor:pointer;display:none'><i class='fa fa-exclamation-triangle' style='color: #fc3;'></i>" +hint1 +"</em> ";
			var varifyingHtml= "<span>"  + verify_code_sent + "</span><input  type='tel' class=' sms-verify-code input-text' style='width:60px'/>";
			var loadingHtml="<i class='fa fa-spinner fa-spin'>";
			var errorHtml= " <em class='start-varify' style='cursor:pointer'><i class='fa fa-exclamation-triangle' style='color: #fc3;'></i>"+hint2 +"</em> "


			
			
			$(context).after(varifiedHtml);
			$(context).after(startVarifiedHtml);

			var phone_number;

			$(context).on('keyup',function(){
				var varified=$(context).data('varified');
				phone_number=$(context).val();

				if(phone_number){
					if(varified){
						$('.varifiedHtml').show();
					}else{
						$('.startVarifiedHtml').show();
					}
				}else{
					$('.varifiedHtml').hide();
					$('.startVarifiedHtml').hide();
				}
			})
			$(context).trigger('keyup');

			$('.start-varify').on('click',function(){
				if($(this).hasClass('varifying'))return;
				$.ajax({
					url:"/member/send_sms_verify_code",
					data:{to:phone_number},
					beforeSend: function( xhr ) {
						$('.start-varify').addClass('varifying');
						$('.varifying').html(loadingHtml);
					}
				}).done(function(result){
					try{
						data = JSON.parse(result);
					}catch(err){
						data['msg'] = result;
					}

					if(data.status==200){
						$('.varifying').html(varifyingHtml);

						$('.sms-verify-code').on('keyup',function(){
							var inputVerifyCode =$(this).val().trim();
							var serverVerifyCode=data['msg'].trim();

							console.log(inputVerifyCode);
							console.log(serverVerifyCode);
							if(inputVerifyCode==serverVerifyCode){
								
								$.ajax({
									url:'/member/update_sms_verified',
									data:{code:inputVerifyCode},
									beforeSend: function( xhr ) {
										$('.varifying').html(loadingHtml);
									}
								}).done(function(result){
									console.log(result);
									$('.varifiedHtml').show();
									$('.varifying').remove();
								}).fail(function(){
									$('.varifying').html(errorHtml);
								})
							}

						})

					}else{
						$('.varifying').html(errorHtml);
					}

				}).fail(function(){
					$('.varifying').html(errorHtml);
				});
			})


		}
	}(jQuery));