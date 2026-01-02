<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('repair_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('job_id')->unique();
            $table->string('customer_name');
            $table->string('customer_type');
            $table->string('mobile');
            $table->date('received_date');
            $table->string('estimated_date')->nullable();
            $table->date('promise_date')->nullable();
            $table->string('priority');
            $table->json('repair_category'); // ✅ Ensure JSON storage
            $table->json('customer_observation')->nullable();
            $table->json('spare_change')->nullable();
            $table->json('job_description')->nullable();
            $table->string('received_by')->nullable();
            $table->json('selected_items')->nullable(); // ✅ Added selected_items
            $table->string('status')->default('not started'); // ✅ New status column
            $table->string('car_image_front')->nullable();
$table->string('car_image_back')->nullable();
$table->string('car_image_left')->nullable();
$table->string('car_image_right')->nullable();
$table->string('car_image_top')->nullable();

            $table->timestamps();
        });
    
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_registration_id')->constrained('repair_registrations')->onDelete('cascade'); // ✅ Foreign key to repairs
            $table->string('plate_no');
            $table->string('model');
            $table->string('vin')->nullable(); // ✅ Added VIN column
            $table->string('condition')->nullable();
            $table->string('tin')->nullable();
            $table->string('year')->nullable();
            $table->string('km_reading')->nullable();
            $table->decimal('estimated_price', 10, 2)->nullable();
            $table->timestamps();
        });
    }
    


    public function down()
    {
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('repair_registrations');
    }
};
