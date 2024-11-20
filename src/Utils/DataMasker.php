<?php

namespace App\Utils;

class DataMasker
{
    public function maskSensitiveData(array $params): array
    {
        if (isset($params['cardNumber'])) {
            $params['cardNumber'] = substr($params['cardNumber'], 0, 6) . str_repeat('*', 6) . substr($params['cardNumber'], -4);
        }

        if (isset($params['cardCvv'])) {
            $params['cardCvv'] = str_repeat('*', strlen($params['cardCvv']));
        }

        return $params;
    }
}
