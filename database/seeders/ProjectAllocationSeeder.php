<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProjectAllocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create departments
        $departments = [
            ['name' => 'Computer Science', 'code' => 'CSC', 'description' => 'Department of Computer Science'],
            ['name' => 'Electrical Engineering', 'code' => 'EEE', 'description' => 'Department of Electrical Engineering'],
            ['name' => 'Mechanical Engineering', 'code' => 'MEE', 'description' => 'Department of Mechanical Engineering'],
            ['name' => 'Civil Engineering', 'code' => 'CVE', 'description' => 'Department of Civil Engineering'],
            ['name' => 'Chemical Engineering', 'code' => 'CHE', 'description' => 'Department of Chemical Engineering'],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }

        // Create specializations
        $specializations = [
            ['name' => 'Artificial Intelligence', 'description' => 'AI and Machine Learning'],
            ['name' => 'Web Development', 'description' => 'Web technologies and frameworks'],
            ['name' => 'Mobile Development', 'description' => 'Mobile app development'],
            ['name' => 'Database Systems', 'description' => 'Database design and management'],
            ['name' => 'Cybersecurity', 'description' => 'Information security and privacy'],
            ['name' => 'Software Engineering', 'description' => 'Software development methodologies'],
            ['name' => 'Data Science', 'description' => 'Data analysis and visualization'],
            ['name' => 'Computer Networks', 'description' => 'Network design and protocols'],
            ['name' => 'Embedded Systems', 'description' => 'Hardware and software integration'],
            ['name' => 'Cloud Computing', 'description' => 'Cloud infrastructure and services'],
            ['name' => 'Power Systems', 'description' => 'Electrical power generation and distribution'],
            ['name' => 'Control Systems', 'description' => 'Automation and control engineering'],
            ['name' => 'Robotics', 'description' => 'Robotic systems and automation'],
            ['name' => 'Structural Engineering', 'description' => 'Building and infrastructure design'],
            ['name' => 'Transportation Engineering', 'description' => 'Roads, bridges, and transportation systems'],
            ['name' => 'Environmental Engineering', 'description' => 'Environmental protection and sustainability'],
            ['name' => 'Process Engineering', 'description' => 'Chemical process design and optimization'],
            ['name' => 'Materials Science', 'description' => 'Materials properties and applications'],
        ];

        foreach ($specializations as $specialization) {
            Specialization::create($specialization);
        }
    }
} 