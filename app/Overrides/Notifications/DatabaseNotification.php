<?php

namespace App\Overrides\Notification;

//Use this if on mongodb.otherwise use to Illuminate\Database\Eloquent\Model
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotificationCollection;

class DatabaseNotification extends Model
{
  protected $connection = 'mongodb';
}
