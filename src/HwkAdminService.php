<?php

namespace Hwkdo\HwkAdminLaravel;


use Hwkdo\HwkAdminLaravel\DTO\SetExchangePermissionDTO;
use Hwkdo\HwkAdminLaravel\DTO\SetExchangeQuotaDTO;
use Illuminate\Support\Facades\Http;

class HwkAdminService
{
    protected $url;
    protected $client;
    protected $outputParsingService;
    public function __construct()
    {
        $this->url = config('hwk-admin-laravel.url');
        $this->client = Http::withoutVerifying()->withHeaders([
            'Authorization' => 'Bearer '.config('hwk-admin-laravel.token'),
            'Accept' => 'application/json',
        ]);
        $this->outputParsingService = app(HwkAdminOutputParsingService::class);
    }

    public function getTasks()
    {
        $response = $this->client->get($this->url.'tasks');
        return $response->json();
    }

    public function getTaskByScriptName($scriptName)
    {
        return collect($this->getTasks())->where('script', $scriptName)->first();
    }

    public function runTask($taskId, $params = [])
    {
        $response = $this->client->post($this->url.'tasks/'.$taskId.'/run', $params);
        return $response->json();
    }    

    public function getExchangePermission($owner_upn)
    {
        $task = $this->getTaskByScriptName('exchange-permission-get');
        $result = $this->runTask($task['id'], ['owner_upn' => $owner_upn]);
        
        return $result['successful'] 
        ? $this->outputParsingService->parseExchangePermissionOutput($result['output']) 
        : $result;        
    }
    
    public function setExchangePermission(SetExchangePermissionDTO $hwkAdminSetExchangePermission)
    {
        $task = $this->getTaskByScriptName('exchange-permission-set');
        return $this->runTask($task['id'], $hwkAdminSetExchangePermission->toArray());
    }

    public function getExchangeQuota($owner_upn)
    {
        $task = $this->getTaskByScriptName('exchange-quota-get');
        $result = $this->runTask($task['id'], ['owner_upn' => $owner_upn]);
        return $result['successful'] 
        ? $this->outputParsingService->parseExchangeQuotaOutput($result['output']) 
        : $result;
    }

    public function setExchangeQuota(SetExchangeQuotaDTO $hwkAdminSetExchangeQuota)
    {
        $task = $this->getTaskByScriptName('exchange-quota-set');
        return $this->runTask($task['id'], $hwkAdminSetExchangeQuota->toArray());
    }

    public function resetExchangePermission($owner_upn)
    {
        $permissions = $this->getExchangePermission($owner_upn);
        $permissions = $permissions->filter(function($permission) {
            return $permission->User != 'NT AUTHORITY\SELF';
        });
        foreach($permissions as $permission) {
            $this->setExchangePermission(new SetExchangePermissionDTO(
                owner_upn: $owner_upn,
                delegate_upn: $permission->User,
                accessRights: $permission->AccessRights[0],
                action: 'Remove'
            ));
        }
        return true;
    }
}