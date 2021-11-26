<?php
function smarty_function_setpath($params)
{
    return $params['obj']->setPath( $params['path'].$params['alias'] );
} 
?>