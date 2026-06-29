<?php

namespace App\Services;

class QrisService
{
    /**
     * User's decoded static QRIS payload.
     */
    protected static string $staticPayload = '00020101021126610014COM.GO-JEK.WWW01189360091436952667940210G6952667940303UMI51440014ID.CO.QRIS.WWW0215ID10254648892740303UMI5204762953033605802ID5925MUHAMMAD ERLANGGA PUTRA W6013JAKARTA TIMUR61051382062070703A0163044E02';

    /**
     * Generate a dynamic QRIS string with the given amount and order code.
     */
    public static function generateDynamic(float $amount, string $orderCode): string
    {
        // 1. Replace 010211 (static initiation) with 010212 (dynamic initiation)
        $payload = str_replace('010211', '010212', self::$staticPayload);

        // Remove the existing CRC tag at the end (the last 4 characters after 6304)
        $pos = strrpos($payload, '6304');
        if ($pos !== false) {
            $payload = substr($payload, 0, $pos + 4);
        }

        // 2. Insert Tag 54 (Amount) before Tag 58 (5802ID)
        $amountStr = (string) round($amount);
        $tag54 = '54' . sprintf('%02d', strlen($amountStr)) . $amountStr;

        $tag58Pos = strpos($payload, '5802ID');
        if ($tag58Pos !== false) {
            $payload = substr($payload, 0, $tag58Pos) . $tag54 . substr($payload, $tag58Pos);
        }

        // 3. Inject Tag 62 with Order Code (Bill Number) and keep terminal info
        $subTag01 = '01' . sprintf('%02d', strlen($orderCode)) . $orderCode;
        $subTag07 = '0703A01';
        $tag62Val = $subTag01 . $subTag07;
        $tag62 = '62' . sprintf('%02d', strlen($tag62Val)) . $tag62Val;

        // Replace old tag 62 "62070703A01" with new tag 62
        $payload = str_replace('62070703A01', $tag62, $payload);

        // 4. Recompute CRC16 Checksum
        $payloadNoCrcVal = substr($payload, 0, -4) . '6304';
        
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($payloadNoCrcVal); $i++) {
            $x = (($crc >> 8) ^ ord($payloadNoCrcVal[$i])) & 0xFF;
            $x ^= $x >> 4;
            $crc = (($crc << 8) ^ ($x << 12) ^ ($x << 5) ^ $x) & 0xFFFF;
        }

        return $payloadNoCrcVal . sprintf('%04X', $crc);
    }
}
