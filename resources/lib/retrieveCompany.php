<?php

use Payever\Sdk\Payments\Http\RequestEntity\CompanySearch\CompanyCustomEntity;
use Payever\Sdk\Payments\Http\RequestEntity\CompanySearch\CompanyEntity;
use Payever\Sdk\Payments\Http\RequestEntity\CompanySearchRequest;
use Payever\Sdk\Payments\Http\RequestEntity\CompanySearch\AddressEntity;

require_once __DIR__ . '/PayeverSdkProvider.php';
$payeverApi = new PayeverSdkProvider(SdkRestApi::getParam('sdkData'));

$company = SdkRestApi::getParam('company');
$country = SdkRestApi::getParam('country');
$type = SdkRestApi::getParam('type');

$companyCustomEntity = new CompanyCustomEntity();
$companyCustomEntity
    ->setValue($company)
    ->setType($type);

$companySearchEntity = new CompanyEntity();
$companySearchEntity->setCustom($companyCustomEntity);

$companySearchRequest = new CompanySearchRequest();
$companySearchRequest->setCompany($companySearchEntity);

$addressEntity = new AddressEntity();
$addressEntity->setCountry($country);

$companySearchRequest->setAddress($addressEntity);

return $payeverApi
    ->getPaymentsApiClient()
    ->searchCompany($companySearchRequest)
    ->getResponseEntity()
    ->toArray();
