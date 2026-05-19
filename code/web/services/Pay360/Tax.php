<?php
/*
 * IMPORTANT: LOCAL only, not intended for submission.
 * Intention is to match behaviour koha-plugin-wscc-oracle's _calculate_exclusive_and_vat
 * will re-evaluate opver testing
 */

class Tax {
    /** @return array{vatAmountInMinorUnits: int , rate: float} */
    public function calculateVat(int $vatInclusiveAmmountInMinorUnits, string $vatCode): array {
        $rate = $this->getVatRate($vatCode);

        // amount in vat derived from: x(1+r) = t <=> x = t/(1+r) <=> x*r = t*r / (1+r) 
        $vatAmountInMinorUnits = round($vatInclusiveAmmountInMinorUnits * $rate / (1 + $rate), 0);
        $rateAsPercentage = 100.0 * $rate;

        return [
            'vatAmountInMinorUnits' => $vatAmountInMinorUnits,
            'rate' => $rateAsPercentage
        ];
    }

    private function getVatRate(string $vatCode): float {
        if ($vatCode == 'STANDARD') {
           return 0.2;
        }
        return 0;
    }
}
