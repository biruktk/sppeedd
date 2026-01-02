<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairRegistration extends Model
{
    use HasFactory;

   protected $fillable = [
    'job_id',
    'customer_name',
    'customer_type',
    'mobile',
    'received_date',
    'estimated_date',
    'promise_date',
    'priority',
    'repair_category',
    'customer_observation',
    'spare_change',
    'job_description',
    'received_by',
    'selected_items',
    'car_image_front',
    'car_image_back',
    'car_image_left',
    'car_image_right',
    'car_image_top',
];


    protected $casts = [
        'repair_category' => 'array',
        'customer_observation' => 'array',
        'spare_change' => 'array',
        'job_description' => 'array',
        'selected_items' => 'array'
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'repair_registration_id');
    }



    // Auto-generate job_id
    protected static function boot()
    {
        parent::boot();
    
        static::creating(function ($repair) {
            $latestJob = static::orderBy('job_id', 'desc')->first();
            $newJobId = $latestJob ? (int) $latestJob->job_id + 1 : 1;
    
            // Format with leading zeros (adjust 4 to your desired length)
            $repair->job_id = str_pad($newJobId, 4, '0', STR_PAD_LEFT);
        });
    }
    
}
