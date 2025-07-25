<?php

namespace Hwkdo\HwkAdminLaravel;

use Hwkdo\HwkAdminLaravel\DTO\GetExchangePermissionOutputDTO;
use Hwkdo\HwkAdminLaravel\DTO\GetExchangeQuotaOutputDTO;

class HwkAdminOutputParsingService
{
    public function cleanOutput($output)
    {
        // Entferne die Header-Informationen (alles vor dem ersten Entry)
        $cleanOutput = preg_replace('/.*?----------------------------------------------------------------------------------------\r\n\n\n/s', '', $output);

        // Entferne ANSI-Farbcodes
        $cleanOutput = preg_replace('/\e\[\d+(?:;\d+)*m/', '', $cleanOutput);

        return $cleanOutput;
    }

    public function splitExchangePermissionOutput($output)
    {
        $entries = preg_split('/(?=IsOwner\s+:)/', $output, -1, PREG_SPLIT_NO_EMPTY);

        return $entries;
    }

    public function parseExchangeQuotaOutput($output)
    {
        // $clean = $this->cleanOutput($output);
        $send = [];
        $sendReceive = [];
        $warning = [];
        preg_match('/ProhibitSendQuota\s+:\s+\\e\[0m([\d.]+) GB/', $output, $send);
        preg_match('/ProhibitSendReceiveQuota\s+:\s+\\e\[0m([\d.]+) GB/', $output, $sendReceive);
        preg_match('/IssueWarningQuota\s+:\s+\\e\[0m([\d.]+) GB/', $output, $warning);

        return new GetExchangeQuotaOutputDTO(
            ProhibitSendQuota: $send[1].'GB',
            ProhibitSendReceiveQuota: $sendReceive[1].'GB',
            IssueWarningQuota: $warning[1].'GB',
        );
    }

    public function parseExchangePermissionOutput($output)
    {
        $clean = $this->cleanOutput($output);
        $entries = $this->splitExchangePermissionOutput($clean);
        $results = [];

        foreach ($entries as $entry) {
            // Extrahiere die Key-Value Paare
            $lines = explode("\n", trim($entry));
            $mailboxPermission = [];

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }

                // Extrahiere Key und Value
                if (preg_match('/^(\w+)\s+:\s+(.*)$/', $line, $matches)) {
                    $key = $matches[1];
                    $value = $matches[2];

                    // Behandle Arrays in geschweiften Klammern
                    if (preg_match('/^\{(.*)\}$/', $value, $arrayMatches)) {
                        $value = array_map('trim', explode(',', $arrayMatches[1]));
                        // Leere Arrays
                        if (count($value) === 1 && empty($value[0])) {
                            $value = [];
                        }
                    }

                    $mailboxPermission[$key] = $value;
                }
            }

            if (! empty($mailboxPermission)) {
                $results[] = $mailboxPermission;
            }
        }

        // return $results;
        return collect($results)->map(fn ($result) => new GetExchangePermissionOutputDTO(
            InheritanceType: $result['InheritanceType'],
            User: $result['User'],
            AccessRights: $result['AccessRights'],
        ));
    }
}
