<?php
/**
 * Created by PhpStorm.
 * User: alikaviani
 * Date: 9/4/18
 * Time: 8:31 PM
 */

use Illuminate\Database\Capsule\Manager as Capsule;
$capsule = new Capsule;
$capsule->addConnection($container['settings']['db']);
// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();
// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();

$container['eloquent'] = function ($container) use ($capsule){
    return $capsule;
};