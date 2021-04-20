<?php

namespace Database\Seeders;

use App\Models\Resource;

use Illuminate\Database\Seeder;

class ResourcesTableSeeder extends Seeder {
    public function run () {
        Resource::create([
            'name'            => 'MktApp',
            'level'           => '1',
            'url'             => '',
            'read_visible'    => '0',
            'write_visible'   => '0',
            'create_visible'  => '0',
            'delete_visible'  => '0',
            'execute_visible' => '1',
            'parent_id'       => '1'
        ]);
        Resource::create([
            'name'            => 'Clients',
            'level'           => '1',
            'url'             => '/clients',
            'read_visible'    => '0',
            'write_visible'   => '0',
            'create_visible'  => '0',
            'delete_visible'  => '0',
            'execute_visible' => '1',
            'parent_id'       => '1'
        ]);
        Resource::create([
            'name'            => 'Media',
            'level'           => '1',
            'url'             => '/media',
            'read_visible'    => '0',
            'write_visible'   => '0',
            'create_visible'  => '0',
            'delete_visible'  => '0',
            'execute_visible' => '1',
            'parent_id'       => '1'
        ]);
        Resource::create([
            'name'            => 'Planification',
            'level'           => '1',
            'url'             => '/planification',
            'read_visible'    => '0',
            'write_visible'   => '0',
            'create_visible'  => '0',
            'delete_visible'  => '0',
            'execute_visible' => '1',
            'parent_id'       => '1'
        ]);
        Resource::create([
            'name'            => 'Reports',
            'level'           => '1',
            'url'             => '/reports',
            'read_visible'    => '0',
            'write_visible'   => '0',
            'create_visible'  => '0',
            'delete_visible'  => '0',
            'execute_visible' => '1',
            'parent_id'       => '1'
        ]);
        Resource::create([
            'name'            => 'Plan',
            'level'           => '2',
            'url'             => '/plan',
            'read_visible'    => '0',
            'write_visible'   => '0',
            'create_visible'  => '0',
            'delete_visible'  => '0',
            'execute_visible' => '1',
            'parent_id'       => '4'
        ]);
        Resource::create([
            'name'            => 'Rate',
            'level'           => '1',
            'url'             => '/rate',
            'read_visible'    => '0',
            'write_visible'   => '0',
            'create_visible'  => '0',
            'delete_visible'  => '0',
            'execute_visible' => '1',
            'parent_id'       => '4'
        ]);
        Resource::create([
            'name'            => 'Campaign',
            'level'           => '1',
            'url'             => '/campaign',
            'read_visible'    => '0',
            'write_visible'   => '0',
            'create_visible'  => '0',
            'delete_visible'  => '0',
            'execute_visible' => '1',
            'parent_id'       => '4'
        ]);
        Resource::create([
            'name'            => 'Guide',
            'level'           => '1',
            'url'             => '/guide',
            'read_visible'    => '0',
            'write_visible'   => '0',
            'create_visible'  => '0',
            'delete_visible'  => '0',
            'execute_visible' => '1',
            'parent_id'       => '4'
        ]);
        Resource::create([
            'name'            => 'Material',
            'level'           => '1',
            'url'             => '/material',
            'read_visible'    => '0',
            'write_visible'   => '0',
            'create_visible'  => '0',
            'delete_visible'  => '0',
            'execute_visible' => '1',
            'parent_id'       => '4'
        ]);
        Resource::create([
            'name'            => 'Administration',
            'level'           => '1',
            'url'             => '/admin',
            'read_visible'    => '0',
            'write_visible'   => '0',
            'create_visible'  => '0',
            'delete_visible'  => '0',
            'execute_visible' => '1',
            'parent_id'       => '1'
        ]);
        Resource::create([
            'name'            => 'User Administration',
            'level'           => '1',
            'url'             => '/users',
            'read_visible'    => '0',
            'write_visible'   => '0',
            'create_visible'  => '0',
            'delete_visible'  => '0',
            'execute_visible' => '1',
            'parent_id'       => '11'
        ]);
    }
}
