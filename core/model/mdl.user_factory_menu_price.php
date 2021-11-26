<?php

class mdl_user_factory_menu_price extends mdl_base
{
    protected $tableName = '#@_user_factory_menu_price';

    public function getUserFactoryPrice($userId, $restaurantMenuId)
    {
        return $this->getByWhere([
            'user_id' => $userId,
            'restaurant_menu_id' => $restaurantMenuId,
        ]);
    }

    public function getUserFactoryPriceList($userId, $factoryId)
    {

        $sql = "SELECT `user_id`, `restaurant_menu_id`, cc_user_factory_menu_price.`price`, cc_restaurant_menu.`restaurant_id`
                FROM cc_user_factory_menu_price 
                LEFT JOIN cc_restaurant_menu ON cc_restaurant_menu.id = cc_user_factory_menu_price.restaurant_menu_id
                WHERE cc_restaurant_menu.restaurant_id = $factoryId 
                AND cc_user_factory_menu_price.user_id = $userId";

        $results = $this->getListBySql($sql);

        $userFactoryPriceList = [];

        foreach ($results as $result) {
            $userFactoryPriceList[$result['restaurant_menu_id']] = [
                'user_id' => $userId,
                'price' => $result['price'],
            ];
        }

        return $userFactoryPriceList;
    }

    public function updateUserFactoryPrice($userId, $restaurantMenuId, $price)
    {
        return $this->updateByWhere([
            'price' => $price,
        ], [
            'user_id' => $userId,
            'restaurant_menu_id' => $restaurantMenuId,
        ]);
    }

    public function insertOrUpdateUserFactoryPrice($userId, $restaurantMenuId, $price)
    {
        $userFactoryMenuPrice = self::getUserFactoryPrice($userId, $restaurantMenuId);
        if($userFactoryMenuPrice) {
            return self::updateUserFactoryPrice($userId, $restaurantMenuId, $price);
        } else {
            return $this->insert([
                'user_id' => $userId,
                'restaurant_menu_id' => $restaurantMenuId,
                'price' => $price
            ]);
        }
    }
}

?>