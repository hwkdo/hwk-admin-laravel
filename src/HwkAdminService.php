<?php

namespace Hwkdo\HwkAdminLaravel;

use Hwkdo\HwkAdminLaravel\DTO\OcrOutputDTO;
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

    public function uploadFile($filepath)
    {        
        $response = $this->client
        ->attach('file', file_get_contents($filepath), 'file')
        ->post($this->url.'temporary-files');       
        return $response->json();
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

    public function createBitwardenSend($name, $content, $maxAccessCount = null, $deleteInDays = null)
    {
        $task = $this->getTaskByScriptName('bitwarden-send');

        $result = $this->runTask($task['id'], [
            'secretName' => $name, 
            'secretContent' => $content,             
        ]);

        // 'maxAccessCount' => $maxAccessCount,
        //     'deleteInDays' => $deleteInDays

        if ($result['successful']) {
            $output = (string) ($result['output'] ?? '');

            $lines = preg_split("/\r\n|\n|\r/", $output) ?: [];
            foreach ($lines as $line) {
                $jsonStartPos = strpos($line, '{');
                if ($jsonStartPos === false) {
                    continue;
                }

                $jsonPart = substr($line, $jsonStartPos);
                $decoded = json_decode($jsonPart, true);

                if (json_last_error() === JSON_ERROR_NONE && isset($decoded['accessUrl'])) {
                    return $decoded['accessUrl'];
                }
            }

            return $output;
        }
        return $result;
    }

    public function resetEntraUserPassword($upn, $mail_empfaenger)
    {
        $task = $this->getTaskByScriptName('entra-password-reset');
        $result = $this->runTask($task['id'], ['userUpn' => $upn, 'mail_empfaenger' => $mail_empfaenger]);

        return $result['successful'];
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
        $permissions = $permissions->filter(function ($permission) {
            return $permission->User != 'NT AUTHORITY\SELF';
        });
        foreach ($permissions as $permission) {
            $this->setExchangePermission(new SetExchangePermissionDTO(
                owner_upn: $owner_upn,
                delegate_upn: $permission->User,
                accessRights: $permission->AccessRights[0],
                action: 'Remove'
            ));
        }

        return true;
    }

    /**
     * OCR a PDF file
     * 
     * @param string $path_to_pdf The path to the PDF file to OCR
     * @return OcrOutputDTO The OCR output
     * the output is a json file with the following structure:
     * {
     *  "success": true,
     *  "data": base64 encoded string of the ocr-ed pdf
     * }
     */
    public function ocr(string $path_to_pdf) : OcrOutputDTO
    {
        $response = $this->client->attach(
            'file',
            file_get_contents($path_to_pdf),
            'file.pdf',
            ['Content-Type' => 'application/pdf']
        )->post($this->url.'pdf/ocr');

        return new OcrOutputDTO(
            success: $response->json()['success'],
            data: $response->json()['data']
        );
    }

    public function ocrToLocalFile(string $path_to_pdf, string $output_path, string $output_filename) : string
    {
        $ocrOutput = $this->ocr($path_to_pdf);
        if ($ocrOutput->success) {
            file_put_contents($output_path.$output_filename, base64_decode($ocrOutput->data));
        }

        return $output_path.$output_filename;
    }

    public function getGewanXml(string $vorgangsnummer) : string
    {
        $task = $this->getTaskByScriptName('formwerk-hwr-antrag-self-service-gewan-xml');

        return $this->runTask($task['id'], ['vorgangsnummer' => $vorgangsnummer]);
    }
}
