<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Drop foreign key constraints from dependent tables if they exist
        // Check and drop tutor_assignments foreign key
        $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'tutor_assignments' AND COLUMN_NAME = 'daily_data_id' 
            AND REFERENCED_TABLE_NAME = 'daily_data' AND TABLE_SCHEMA = DATABASE()");
        
        if (!empty($foreignKeys)) {
            $constraintName = $foreignKeys[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE tutor_assignments DROP FOREIGN KEY `{$constraintName}`");
        }

        // Check and drop schedule_history foreign key
        $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'schedule_history' AND COLUMN_NAME = 'class_id' 
            AND REFERENCED_TABLE_NAME = 'daily_data' AND TABLE_SCHEMA = DATABASE()");
        
        if (!empty($foreignKeys)) {
            $constraintName = $foreignKeys[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE schedule_history DROP FOREIGN KEY `{$constraintName}`");
        }

        // Check and drop supervisor_watches foreign key
        $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'supervisor_watches' AND COLUMN_NAME = 'daily_data_id' 
            AND REFERENCED_TABLE_NAME = 'daily_data' AND TABLE_SCHEMA = DATABASE()");
        
        if (!empty($foreignKeys)) {
            $constraintName = $foreignKeys[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE supervisor_watches DROP FOREIGN KEY `{$constraintName}`");
        }

        // Step 2: Create the new schedules_daily_data table with simplified structure
        Schema::create('schedules_daily_data', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('day');
            $table->time('time');
            $table->integer('duration')->default(25);
            $table->string('school');
            $table->string('class');
            $table->timestamps();

            // Indexes for performance
            $table->index('date');
            $table->index(['school', 'class']);
            $table->unique(['school', 'class', 'date', 'time']);
        });

        // Step 2: Create the assigned_daily_data table for assignment details
        Schema::create('assigned_daily_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_daily_data_id');
            $table->enum('class_status', ['active', 'cancelled'])->default('active');
            $table->unsignedBigInteger('main_tutor')->nullable();
            $table->unsignedBigInteger('backup_tutor')->nullable();
            $table->unsignedBigInteger('assigned_supervisor')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->unsignedBigInteger('finalized_by')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign key to schedules_daily_data
            $table->foreign('schedule_daily_data_id')
                  ->references('id')
                  ->on('schedules_daily_data')
                  ->onDelete('cascade');

            // Foreign keys to tutor table
            $table->foreign('main_tutor')
                  ->references('tutor_id')
                  ->on('tutor')
                  ->onDelete('set null');

            $table->foreign('backup_tutor')
                  ->references('tutor_id')
                  ->on('tutor')
                  ->onDelete('set null');

            // Foreign keys to supervisor table
            $table->foreign('assigned_supervisor')
                  ->references('supervisor_id')
                  ->on('supervisor')
                  ->onDelete('set null');

            $table->foreign('finalized_by')
                  ->references('supervisor_id')
                  ->on('supervisor')
                  ->onDelete('set null');

            // Indexes
            $table->index('schedule_daily_data_id');
            $table->index('class_status');
            $table->index('main_tutor');
            $table->index('backup_tutor');
        });

        // Step 4: Migrate existing data from daily_data to new structure
        if (Schema::hasTable('daily_data')) {
            $existingData = DB::table('daily_data')->get();

            // Create a mapping between old IDs and new IDs
            $idMapping = [];

            foreach ($existingData as $row) {
                // Insert into schedules_daily_data (basic schedule info)
                $scheduleId = DB::table('schedules_daily_data')->insertGetId([
                    'date' => $row->date,
                    'day' => $row->day,
                    'time' => $row->time_pht ?? $row->time_jst,
                    'duration' => $row->duration ?? 25,
                    'school' => $row->school,
                    'class' => $row->class,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);

                // Store mapping for foreign key updates
                $idMapping[$row->id] = $scheduleId;

                // Get main tutor from tutor_assignments if exists
                $mainTutor = DB::table('tutor_assignments')
                    ->where('daily_data_id', $row->id)
                    ->value('tutor_id');

                // Insert into assigned_daily_data (assignment details)
                DB::table('assigned_daily_data')->insert([
                    'schedule_daily_data_id' => $scheduleId,
                    'class_status' => $row->class_status ?? 'active',
                    'main_tutor' => $mainTutor,
                    'backup_tutor' => null,
                    'assigned_supervisor' => $row->assigned_supervisor ?? null,
                    'finalized_at' => $row->finalized_at ?? null,
                    'finalized_by' => $row->finalized_by ?? null,
                    'cancelled_at' => $row->cancelled_at ?? null,
                    'notes' => $row->cancellation_reason ?? null,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }

            // Step 5: Update foreign keys in dependent tables
            foreach ($idMapping as $oldId => $newId) {
                // Update tutor_assignments
                DB::table('tutor_assignments')
                    ->where('daily_data_id', $oldId)
                    ->update(['daily_data_id' => $newId]);

                // Update schedule_history
                DB::table('schedule_history')
                    ->where('class_id', $oldId)
                    ->update(['class_id' => $newId]);

                // Update supervisor_watches
                DB::table('supervisor_watches')
                    ->where('daily_data_id', $oldId)
                    ->update(['daily_data_id' => $newId]);
            }

            // Step 6: Drop old daily_data table
            Schema::dropIfExists('daily_data');
        }

        // Step 7: Re-add foreign key constraints pointing to new table
        if (Schema::hasTable('tutor_assignments')) {
            Schema::table('tutor_assignments', function (Blueprint $table) {
                $table->foreign('daily_data_id')
                      ->references('id')
                      ->on('schedules_daily_data')
                      ->onDelete('cascade');
            });
        }

        if (Schema::hasTable('schedule_history')) {
            Schema::table('schedule_history', function (Blueprint $table) {
                $table->foreign('class_id')
                      ->references('id')
                      ->on('schedules_daily_data')
                      ->onDelete('cascade');
            });
        }

        if (Schema::hasTable('supervisor_watches')) {
            Schema::table('supervisor_watches', function (Blueprint $table) {
                $table->foreign('daily_data_id')
                      ->references('id')
                      ->on('schedules_daily_data')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate daily_data table
        Schema::create('daily_data', function (Blueprint $table) {
            $table->id();
            $table->string('school');
            $table->string('class');
            $table->integer('duration')->default(25);
            $table->date('date');
            $table->string('day')->nullable();
            $table->time('time_jst')->nullable();
            $table->time('time_pht')->nullable();
            $table->integer('number_required')->default(1);
            $table->enum('schedule_status', ['draft','tentative','finalized'])->default('draft');
            $table->timestamp('finalized_at')->nullable();
            $table->unsignedBigInteger('finalized_by')->nullable();
            $table->string('assigned_supervisor')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->enum('class_status', ['active','cancelled'])->default('active');
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
        });

        // Drop new tables
        Schema::dropIfExists('assigned_daily_data');
        Schema::dropIfExists('schedules_daily_data');
    }
};
