<?php

namespace App\Modules\Opportunity\Domain\Catalogue;

/**
 * Sample investment catalogue. Sector codes own their sub-sectors.
 * Enterprise types follow the opportunity data requirement, with a few
 * additional sample types carried from the client demonstration.
 */
final class InvestmentCatalogue
{
    /**
     * @return list<array{code: string, name: string, sort_order: int}>
     */
    public static function sectors(): array
    {
        return [
            ['code' => 'AGR', 'name' => 'Agriculture and agro-processing', 'sort_order' => 1],
            ['code' => 'ENE', 'name' => 'Renewable energy', 'sort_order' => 2],
            ['code' => 'LOG', 'name' => 'Transport and logistics', 'sort_order' => 3],
            ['code' => 'MAN', 'name' => 'Manufacturing', 'sort_order' => 4],
            ['code' => 'TOU', 'name' => 'Tourism and hospitality', 'sort_order' => 5],
            ['code' => 'ICT', 'name' => 'Information and communication technology', 'sort_order' => 6],
            ['code' => 'HEA', 'name' => 'Healthcare and pharmaceuticals', 'sort_order' => 7],
            ['code' => 'HOU', 'name' => 'Housing and real estate', 'sort_order' => 8],
            ['code' => 'MIN', 'name' => 'Mining and mineral processing', 'sort_order' => 9],
            ['code' => 'EDU', 'name' => 'Education and skills development', 'sort_order' => 10],
        ];
    }

    /**
     * @return list<array{code: string, name: string, sector: string, sort_order: int}>
     */
    public static function subSectors(): array
    {
        return [
            ['code' => 'AGR-CROP', 'name' => 'Crop production', 'sector' => 'AGR', 'sort_order' => 1],
            ['code' => 'AGR-PROC', 'name' => 'Food and agro-processing', 'sector' => 'AGR', 'sort_order' => 2],
            ['code' => 'AGR-LIVE', 'name' => 'Livestock and poultry', 'sector' => 'AGR', 'sort_order' => 3],
            ['code' => 'ENE-SOLAR', 'name' => 'Solar power', 'sector' => 'ENE', 'sort_order' => 1],
            ['code' => 'ENE-BIO', 'name' => 'Bioenergy and waste-to-energy', 'sector' => 'ENE', 'sort_order' => 2],
            ['code' => 'LOG-PORT', 'name' => 'Ports and maritime logistics', 'sector' => 'LOG', 'sort_order' => 1],
            ['code' => 'LOG-WARE', 'name' => 'Warehousing and cold chain', 'sector' => 'LOG', 'sort_order' => 2],
            ['code' => 'LOG-ROAD', 'name' => 'Road and transit infrastructure', 'sector' => 'LOG', 'sort_order' => 3],
            ['code' => 'MAN-AUTO', 'name' => 'Automotive and equipment assembly', 'sector' => 'MAN', 'sort_order' => 1],
            ['code' => 'MAN-TEXT', 'name' => 'Textiles and apparel', 'sector' => 'MAN', 'sort_order' => 2],
            ['code' => 'MAN-PACK', 'name' => 'Packaging and consumer goods', 'sector' => 'MAN', 'sort_order' => 3],
            ['code' => 'TOU-ECO', 'name' => 'Eco and cultural tourism', 'sector' => 'TOU', 'sort_order' => 1],
            ['code' => 'TOU-HOSP', 'name' => 'Hotels and visitor services', 'sector' => 'TOU', 'sort_order' => 2],
            ['code' => 'ICT-FIN', 'name' => 'Financial technology', 'sector' => 'ICT', 'sort_order' => 1],
            ['code' => 'ICT-BPO', 'name' => 'Business process outsourcing', 'sector' => 'ICT', 'sort_order' => 2],
            ['code' => 'ICT-DATA', 'name' => 'Data centres and cloud services', 'sector' => 'ICT', 'sort_order' => 3],
            ['code' => 'HEA-CARE', 'name' => 'Clinical and diagnostic services', 'sector' => 'HEA', 'sort_order' => 1],
            ['code' => 'HEA-PHAR', 'name' => 'Pharmaceutical manufacturing', 'sector' => 'HEA', 'sort_order' => 2],
            ['code' => 'HOU-AFF', 'name' => 'Affordable housing', 'sector' => 'HOU', 'sort_order' => 1],
            ['code' => 'HOU-COM', 'name' => 'Commercial and industrial property', 'sector' => 'HOU', 'sort_order' => 2],
            ['code' => 'MIN-GOLD', 'name' => 'Gold and precious minerals', 'sector' => 'MIN', 'sort_order' => 1],
            ['code' => 'MIN-IND', 'name' => 'Industrial minerals and processing', 'sector' => 'MIN', 'sort_order' => 2],
            ['code' => 'EDU-TVET', 'name' => 'Technical and vocational education', 'sector' => 'EDU', 'sort_order' => 1],
            ['code' => 'EDU-DIG', 'name' => 'Digital learning and training', 'sector' => 'EDU', 'sort_order' => 2],
        ];
    }

    /**
     * @return list<array{code: string, name: string}>
     */
    public static function enterpriseTypes(): array
    {
        return [
            ['code' => 'SP', 'name' => 'Sole proprietorship'],
            ['code' => 'JV', 'name' => 'Joint venture / partnership'],
            ['code' => 'PLL', 'name' => 'Private limited liability'],
            ['code' => 'PUB', 'name' => 'Public limited liability'],
            ['code' => 'COOP', 'name' => 'Cooperative society'],
            ['code' => 'SOE', 'name' => 'State-owned enterprise'],
            ['code' => 'SPV', 'name' => 'Special purpose vehicle'],
            ['code' => 'SOC', 'name' => 'Social enterprise'],
        ];
    }
}
