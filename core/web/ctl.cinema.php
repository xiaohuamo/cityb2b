<?php
	class ctl_cinema extends cmsPage
	{

		function cinema_edit_action(){
			$data=$this->loadModel('cinema')->getLocationData($this->loginUser['id']);

			$this->setData( $data, 'locationList' );
			$this->setData( 'movie', 'menu' );
			$this->setData( 'cinema_edit', 'submenu' );

			$this->display("cinema/cinema_edit");
		}

		function cinema_remove_action(){
			 $this->loadModel('cinema')->deleteLocation(get2('id'));
			 $this->sheader(HTTP_ROOT_WWW.'cinema/cinema_edit');
		}

		function location_add_action(){
			$locationName = trim(post('location'));
			$room = trim(post('room'));
			$type = trim(post('type'));
			$total = trim(post('total'));

			$mdl_cinema=$this->loadModel('cinema');

			//Location
			$location = new Location();
			$location->setUser($this->loginUser['id']);
			$location->setLocation($locationName);
			$location->setRoomName($room);
			$location->setRoomType($type);
			$location->setTotalSeats($total);

			if($mdl_cinema->addNewLocation($location)){
				$this->form_response(200,'修改成功',HTTP_ROOT_WWW.'cinema/cinema_edit');
			}else{
				$this->form_response_msg('failed');
			}


		}

		function price_group_edit_action(){

			$data= $this->loadModel('cinema')->getPriceGroupList($this->loginUser['id']);
			$this->setData($data,'priceGroupList');

			$this->setData( 'movie', 'menu' );
			$this->setData( 'price_group_edit', 'submenu' );

			$this->display("cinema/price_group_edit");
		}
		function price_group_remove_action(){
			$id = get2('id');
			$this->loadModel('cinema')->deletePriceGroup($id);
			 $this->sheader(HTTP_ROOT_WWW.'cinema/price_group_edit');
		}

		function price_group_add_action(){
			$labelname = trim(post('labelname'));
			$items = post('item');

			$mdl_cinema=$this->loadModel('cinema');

			$priceGroup = new PriceGroup();
			$priceGroup->setUser($this->loginUser['id']);
			$priceGroup->setLableName($labelname);
			
			foreach ($items as $key => $value) {
				if($value['room']==null ||$value['type']==null )continue;
				$priceGroup->addPrice($value['room'],$value['type']);
			}

			if($mdl_cinema->addNewPriceGroup($priceGroup)){
				$this->form_response(200,'修改成功',HTTP_ROOT_WWW.'cinema/price_group_edit');
			}else{
				$this->form_response_msg('failed');
			}


		}

		function movie_edit_action(){
			$couponList = $this->loadModel('coupons')->getCouponListOfType($this->loginUser['id'],11);
			$this->setData($couponList,'couponList');

			$mdl_cinema=$this->loadModel('cinema');
			$this->setData($mdl_cinema->getLocationList($this->loginUser['id']),'locationList');

			$this->setData($mdl_cinema->getLocationData($this->loginUser['id']),'locationData');

			$this->setData($mdl_cinema->getPriceGroupList($this->loginUser['id']),'priceGroupList');
			
			$this->setData($mdl_cinema->getMovieListByUser($this->loginUser['id']),'movieList');

			$this->setData( 'movie', 'menu' );
			$this->setData( 'movie_edit', 'submenu' );

			$this->display('cinema/movie_edit');
		}

		function movie_add_action(){

			$couponId = trim(post('couponId'));
			$locationId = trim(post('room'));
			$date = trim(post('date'));
			$time = trim(post('time'));
			$priceGroupId = trim(post('priceGroup'));

			$mdl_cinema=$this->loadModel('cinema');
			//movie
			$movie = new Movie();
			$movie->setCouponId($couponId);
			$movie->setCreateUserId($this->loginUser['id']);
			$movie->setLocation($locationId);
			$movie->setDate($date);
			$movie->setTime($time);
			$movie->setTimeStamp(strtotime($date.' '.$time));
			$movie->setPriceGroup($mdl_cinema->getPriceGroupData($priceGroupId));

			if($mdl_cinema->addNewMovie($movie)){
				$this->form_response(200,'修改成功',HTTP_ROOT_WWW.'cinema/movie_edit');
			}else{
				$this->form_response_msg('failed');
			}
		}


		function movie_remove_action(){
			 $this->loadModel('cinema')->deleteMovie(get2('id'));
			 $this->sheader(HTTP_ROOT_WWW.'cinema/movie_edit');
		}

		function test_action(){
			$mdl_cinema=$this->loadModel('cinema');
			echo '<pre>';
			//price group
			$priceGroup = new PriceGroup();
			$priceGroup->setUser(22);
			$priceGroup->setLableName('价格组1');
			$priceGroup->addPrice('Child',12.5);
			$priceGroup->addPrice('Consession',15);
			$priceGroup->addPrice('Adult',20);

			$mdl_cinema->addNewPriceGroup($priceGroup);

			$data=$mdl_cinema->getPriceGroupList(22);
			var_dump($data);

			foreach ($data as $key => $value) {
				$mdl_cinema->deletePriceGroup($value['id']);
			}
			

			//Location
			$location = new Location();
			$location->setUser(22);
			$location->setLocation("Boxhill");
			$location->setRoomName("放映厅2");
			$location->setRoomType("Lux");
			$location->setTotalSeats(100);

			$mdl_cinema->addNewLocation($location);


			$location = new Location();
			$location->setUser(22);
			$location->setLocation("Boxhill");
			$location->setRoomName("放映厅1");
			$location->setRoomType("Lux");
			$location->setTotalSeats(100);

			$mdl_cinema->addNewLocation($location);


			$location = new Location();
			$location->setUser(22);
			$location->setLocation("Clayton");
			$location->setRoomName("放映厅1");
			$location->setRoomType("Lux");
			$location->setTotalSeats(100);

			$mdl_cinema->addNewLocation($location);

			$mdl_cinema->getLocationList(22);
			var_dump($mdl_cinema->getLocationList(22));

			$data=$mdl_cinema->getRoomList(22,"Boxhill");
			var_dump($data);

			foreach ($data as $key => $value) {
				$mdl_cinema->deleteLocation($value['id']);
			}

			$data=$mdl_cinema->getRoomList(22,"Clayton");
			var_dump($data);

			foreach ($data as $key => $value) {
				$mdl_cinema->deleteLocation($value['id']);
			}

			//movie
			$movie = new Movie();
			$movie->setCouponId(10001);
			$movie->setLocation(22);
			$movie->setDate('2012-06-18 Sunday');
			$movie->setTime(' 10:34:09 AM');
			$movie->setTimeStamp(time());

			$customizedPriceGroup = new PriceGroup();
			$customizedPriceGroup->addPrice('Child',12.5);
			$customizedPriceGroup->addPrice('Consession',15);
			$customizedPriceGroup->addPrice('Adult',20);

			$movie->setPriceGroup($customizedPriceGroup->getPriceGroup());

			$mdl_cinema->addNewMovie($movie);

			$data = $mdl_cinema->getMovieList(10001);
			var_dump($data);

			foreach ($data as $key => $value) {
				$data = $mdl_cinema->deleteMovie($value['id']);
			}
		}
	}
?>