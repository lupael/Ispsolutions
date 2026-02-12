<?php

namespace App\Services;

use App\Contracts\MikrotikServiceInterface;
use RouterOS\Sohag\RouterosAPI;

class MikrotikService implements MikrotikServiceInterface
{
    protected $api;

    public function connect($ip, $username, $password, $port = 8728)
    {
        $config = [
            'host' => $ip,
            'user' => $username,
            'pass' => $password,
            'port' => (int)$port,
            'attempts' => 1
        ];

        $this->api = new RouterosAPI($config);
        return $this->api->connect($config['host'], $config['user'], $config['pass']);
    }

    public function getPppActive()
    {
        return $this->api->getMktRows('ppp_active');
    }

    public function getHotspotActive()
    {
        return $this->api->getMktRows('hotspot_active');
    }

    public function disconnect()
    {
        $this->api->disconnect();
    }

    public function configureRouter($router, $radiusServerIp)
    {
        // 1. RADIUS Settings
        $this->api->addMktRows('radius', [
            [
                'address' => $radiusServerIp,
                'authentication-port' => 3612,
                'accounting-port' => 3613,
                'secret' => $router->secret,
                'service' => 'hotspot,ppp',
                'timeout' => '3s',
                'require-message-auth' => 'no',
            ]
        ]);

        // 2. System Identity (Optional)
        // Assuming company name is available in operator settings
        // $this->api->ttyWirte('/system/identity/set', ['name' => $router->operator->company_in_native_lang . '-' . $router->shortname]);

        // 3. Firewall NAT Rules (Hotspot)
        $this->api->addMktRows('ip_firewall_nat', [
            [
                'chain' => 'pre-hotspot',
                'dst-address-type' => '!local',
                'hotspot' => 'auth',
                'action' => 'accept',
                'comment' => 'bypassed auth',
            ]
        ]);

        // 4. Walled Garden (Hotspot)
        $this->api->addMktRows('walled_garden_ip', [
            [
                'action' => 'accept',
                'dst-address' => $radiusServerIp,
                'comment' => 'Radius Server',
            ]
        ]);

        // 5. Hotspot Server Settings
        $this->api->ttyWirte('/ip/hotspot/set', ['idle-timeout' => '5m', 'keepalive-timeout' => 'none', 'login-timeout' => 'none']);

        // 6. Hotspot Profile Settings
        $this->api->ttyWirte('/ip/hotspot/profile/set', [
            'login-by' => 'mac,cookie,http-chap,http-pap,mac-cookie',
            'mac-auth-mode' => 'mac-as-username-and-password',
            'http-cookie-lifetime' => '6h',
            'split-user-domain' => 'no',
            'use-radius' => 'yes',
            'radius-accounting' => 'yes',
            'radius-interim-update' => '5m',
            'nas-port-type' => 'wireless-802.11',
            'radius-mac-format' => 'XX:XX:XX:XX:XX:XX',
        ]);

        // 7. Hotspot User Profile Settings
        $onLoginScript = ':foreach n in=[/queue simple find comment=priority_1] do={ /queue simple move $n [:pick [/queue simple find] 0] }';
        $onLogoutScript = '/ip hotspot host remove [find where address=$address and !authorized and !bypassed]';
        $this->api->ttyWirte('/ip/hotspot/user/profile/set', [
            'idle-timeout' => 'none',
            'keepalive-timeout' => '2m',
            'queue-type' => 'hotspot-default',
            'on-login' => $onLoginScript,
            'on-logout' => $onLogoutScript,
        ]);

        // 8. PPPoE Server Settings
        $this->api->ttyWirte('/ppp/profile/set', ['default' => ['local-address' => '10.0.0.1']]);
        $this->api->ttyWirte('/pppoe-server/server/set', [
            'authentication' => 'pap,chap',
            'one-session-per-host' => 'yes',
            'default-profile' => 'default',
        ]);

        // 9. PPP AAA Settings
        $this->api->ttyWirte('/ppp/aaa/set', [
            'interim-update' => '5m',
            'use-radius' => 'yes',
            'accounting' => 'yes',
        ]);

        // 10. PPP Profile On-Up Script
        $pppOnUpScript = ':local sessions [/ppp active print count-only where name=$user]; :if ( $sessions > 1) do={ :log info ("disconnecting " . $user  ." duplicate" ); /ppp active remove [find where (name=$user && uptime<00:00:30 )]; }';
        $this->api->ttyWirte('/ppp/profile/set', ['default' => ['on-up' => $pppOnUpScript]]);


        // 11. Suspended Users Pool
        $this->api->addMktRows('ip_pool', [
            [
                'name' => 'suspended-pool',
                'ranges' => '100.65.96.0/20',
            ]
        ]);

        // 12. RADIUS Incoming
        $this->api->ttyWirte('/radius/incoming/set', ['accept' => 'yes']);

        // 13. SNMP Configuration
        $this->api->ttyWirte('/snmp/set', ['enabled' => 'yes']);
        $this->api.addMktRows('snmp_community', [['name' => 'billing']]);

        // 14. Firewall Rules for Suspended Pool
        $this->api->addMktRows('ip_firewall_filter', [
            [
                'chain' => 'forward',
                'src-address' => '100.65.96.0/20',
                'action' => 'drop',
                'comment' => 'drop suspended pool',
            ],
            [
                'chain' => 'input',
                'src-address' => '100.65.96.0/20',
                'action' => 'drop',
                'comment' => 'drop suspended pool',
            ]
        ]);
    }
}