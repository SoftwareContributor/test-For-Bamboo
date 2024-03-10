<?php

class Travel
{
    public static function fetchTravelData()
    {
        $url = 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/travels';
        $context = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $data = @file_get_contents($url, false, $context);
        if ($data === false) {
            die("Failed to fetch travel data from API.");
        }
        $decoded_data = json_decode($data, true);
        if ($decoded_data === null) {
            die("Failed to decode travel data: " . json_last_error_msg());
        }
        return $decoded_data;
    }
}

class Company
{
    public static function fetchCompanyData()
    {
        $url = 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/companies';
        $context = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $data = @file_get_contents($url, false, $context);
        if ($data === false) {
            die("Failed to fetch company data from API.");
        }
        $decoded_data = json_decode($data, true);
        if ($decoded_data === null) {
            die("Failed to decode company data: " . json_last_error_msg());
        }
        return $decoded_data;
    }

    public static function buildCompanyTree($companies, $travels)
    {
        $companyTree = [];

        foreach ($companies as $company) {
            $company['cost'] = self::calculateTravelCost($company['id'], $travels);
            $company['children'] = self::getChildCompanies($company['id'], $companies, $travels);
            $companyTree[] = $company;
        }

        return $companyTree;
    }

    private static function calculateTravelCost($companyId, $travels)
    {
        $totalCost = 0;

        foreach ($travels as $travel) {
            if ($travel['companyId'] == $companyId) {
                $totalCost += $travel['travelPrice'];
            }
        }

        return $totalCost;
    }

    private static function getChildCompanies($parentId, $companies, $travels)
    {
        $children = [];

        foreach ($companies as $company) {
            if ($company['parentId'] == $parentId) {
                $company['cost'] = self::calculateTravelCost($company['id'], $travels);
                $company['children'] = self::getChildCompanies($company['id'], $companies, $travels);
                $children[] = $company;
            }
        }

        return $children;
    }
}

class TestScript
{
    public function execute()
    {
        $start = microtime(true);

        $companies = Company::fetchCompanyData();
        $travels = Travel::fetchTravelData();
        $result = Company::buildCompanyTree($companies, $travels);

        echo json_encode($result, JSON_PRETTY_PRINT);

        echo 'Total time: ' . (microtime(true) - $start);
    }
}

(new TestScript())->execute();
?>
