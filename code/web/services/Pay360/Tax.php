<?php
/*
 * IMPORTANT: LOCAL only, not intended for submission.
 * Intention is to match behaviour koha-plugin-wscc-oracle's _calculate_exclusive_and_vat
 * will re-evaluate opver testing
 */

class Tax {

    # REFERENCE ONLY Map database SAP VAT codes to rates
    // const VAT_CODES = [
    //     'A7' => 0,          // out of scope -> 0 tax rate
    //     'A8' => 0,          // zero rate -> 0 tax rate
    //     'AE' => 0,          // exempt -> 0 tax rate
    //     'AW' => 0.2,        // standard-> 0.2 tax rate
    // ];

    /** @return array{vatAmountInMinorUnits: int , rate: float} */
    public function calculateVat(int $vatInclusiveAmountInMinorUnits, string $vatCode): array {
        $rate = $this->getVatRate($vatCode);

        // amount in vat derived from: x(1+r) = t <=> x = t/(1+r) <=> x*r = t*r / (1+r) 
        $vatAmountInMinorUnits = round($vatInclusiveAmountInMinorUnits * $rate / (1 + $rate), 0);
        $rateAsPercentage = 100.0 * $rate;

        return [
            'vatAmountInMinorUnits' => (int)$vatAmountInMinorUnits,
            'rate' => $rateAsPercentage
        ];
    }

    private function getVatRate(string $vatCode): float {
        if ($vatCode == 'AW') {
           return 0.2;
        }
        return 0;
    }
}
