<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\RouterConfigurationTemplate;
use Illuminate\Database\Seeder;

class RouterConfigurationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Full ISP Provisioning',
                'description' => 'Complete zero-touch provisioning with RADIUS, Hotspot, PPPoE, NAT, Firewall, and System configuration',
                'template_type' => 'full_provisioning',
                'is_default' => true,
                'configuration' => [
                    'system' => [
                        'identity' => '{{ system_identity }}',
                        'ntp_servers' => ['{{ ntp_server }}'],
                        'timezone' => '{{ timezone }}',
                    ],
                    'radius' => [
                        'server' => '{{ radius_server }}',
                        'secret' => '{{ radius_secret }}',
                        'auth_port' => 1812,
                        'acct_port' => 1813,
                        'timeout' => '3s',
                        'service' => 'ppp,hotspot',
                    ],
                    'hotspot' => [
                        'profile_name' => 'default',
                        'hotspot_address' => '{{ hotspot_address }}',
                        'dns_name' => '{{ dns_name }}',
                        'login_by' => 'mac,http-chap',
                        'use_radius' => true,
                        'mac_auth_mode' => 'mac-as-username',
                        'cookie_timeout' => '3d',
                        'idle_timeout' => 'none',
                        'keepalive_timeout' => '2m',
                        'shared_users' => 1,
                    ],
                    'pppoe' => [
                        'service_name' => 'pppoe',
                        'interface' => 'ether2',
                        'default_profile' => 'default',
                        'authentication' => 'pap,chap,mschap1,mschap2',
                        'keepalive_timeout' => 10,
                        'one_session_per_host' => true,
                        'max_sessions' => 1000,
                        'ip_pool' => [
                            'name' => 'pppoe-pool',
                            'ranges' => '{{ pppoe_pool_start }}-{{ pppoe_pool_end }}',
                        ],
                    ],
                    'nat' => [
                        'rules' => [
                            [
                                'chain' => 'srcnat',
                                'action' => 'masquerade',
                                'out_interface' => 'ether1',
                                'comment' => 'Internet masquerade',
                            ],
                            [
                                'chain' => 'dstnat',
                                'protocol' => 'tcp',
                                'dst_port' => '80',
                                'action' => 'redirect',
                                'to_ports' => '8080',
                                'comment' => 'Hotspot redirect',
                            ],
                        ],
                    ],
                    'firewall' => [
                        'rules' => [
                            [
                                'chain' => 'input',
                                'protocol' => 'udp',
                                'dst_port' => '161',
                                'action' => 'accept',
                                'comment' => 'Allow SNMP',
                            ],
                            [
                                'chain' => 'input',
                                'protocol' => 'tcp',
                                'dst_port' => '22',
                                'action' => 'accept',
                                'comment' => 'Allow SSH',
                            ],
                            [
                                'chain' => 'forward',
                                'src_address' => '10.255.255.0/24',
                                'action' => 'drop',
                                'comment' => 'Block suspended users',
                            ],
                        ],
                    ],
                    'walled_garden' => [
                        'entries' => [
                            [
                                'host' => '{{ central_server_ip }}',
                                'action' => 'allow',
                                'comment' => 'Central server',
                            ],
                            [
                                'host' => '*.google.com',
                                'action' => 'allow',
                                'comment' => 'Allow Google DNS',
                            ],
                        ],
                        'ip_entries' => [
                            [
                                'address' => '{{ central_server_ip }}',
                                'action' => 'allow',
                                'comment' => 'Central server IP',
                            ],
                            [
                                'address' => '8.8.8.8',
                                'action' => 'allow',
                                'comment' => 'Google DNS',
                            ],
                        ],
                    ],
                    'suspended_pool' => [
                        'pool_name' => 'suspended-pool',
                        'pool_range' => '10.255.255.2-10.255.255.254',
                        'pool_network' => '10.255.255.0/24',
                        'redirect_url' => 'http://{{ central_server_ip }}/recharge',
                    ],
                ],
            ],
            [
                'name' => 'RADIUS Only',
                'description' => 'Configure only RADIUS authentication settings',
                'template_type' => 'radius',
                'configuration' => [
                    'radius' => [
                        'server' => '{{ radius_server }}',
                        'secret' => '{{ radius_secret }}',
                        'auth_port' => 1812,
                        'acct_port' => 1813,
                        'timeout' => '3s',
                        'service' => 'ppp,hotspot',
                    ],
                ],
            ],
            [
                'name' => 'Hotspot Profile',
                'description' => 'Configure hotspot profile with RADIUS authentication',
                'template_type' => 'hotspot',
                'configuration' => [
                    'hotspot' => [
                        'profile_name' => 'default',
                        'hotspot_address' => '{{ hotspot_address }}',
                        'dns_name' => '{{ dns_name }}',
                        'login_by' => 'mac,http-chap',
                        'use_radius' => true,
                        'mac_auth_mode' => 'mac-as-username',
                        'cookie_timeout' => '3d',
                        'idle_timeout' => 'none',
                        'keepalive_timeout' => '2m',
                        'shared_users' => 1,
                    ],
                ],
            ],
            [
                'name' => 'PPPoE Server',
                'description' => 'Configure PPPoE server with IP pool',
                'template_type' => 'pppoe',
                'configuration' => [
                    'pppoe' => [
                        'service_name' => 'pppoe',
                        'interface' => 'ether2',
                        'default_profile' => 'default',
                        'authentication' => 'pap,chap,mschap1,mschap2',
                        'keepalive_timeout' => 10,
                        'one_session_per_host' => true,
                        'max_sessions' => 1000,
                        'ip_pool' => [
                            'name' => 'pppoe-pool',
                            'ranges' => '{{ pppoe_pool_start }}-{{ pppoe_pool_end }}',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'System Configuration',
                'description' => 'Configure system identity, NTP, and timezone',
                'template_type' => 'system',
                'configuration' => [
                    'system' => [
                        'identity' => '{{ system_identity }}',
                        'ntp_servers' => ['{{ ntp_server }}'],
                        'timezone' => '{{ timezone }}',
                    ],
                ],
            ],
        ];

        foreach ($templates as $template) {
            RouterConfigurationTemplate::updateOrCreate(
                [
                    'name' => $template['name'],
                    'template_type' => $template['template_type'],
                ],
                $template
            );
        }

        $this->command->info('Router configuration templates seeded successfully!');
    }
}
