<?php
namespace app\services;

use app\models\ClientRequirement;
use app\models\Organization;
use app\models\Requirement;
use Yii;

class RequirementBuilder
{
    /**
     * Auto-generate client requirements based on organization profile
     */
    public static function build(Organization $org): void
    {
        // Delete existing to rebuild
        ClientRequirement::deleteAll(['org_id' => $org->id]);

        $requirements = Requirement::find()->all();

        foreach ($requirements as $req) {
            if (self::matches($org, $req)) {
                $cr = new ClientRequirement();
                $cr->org_id = $org->id;
                $cr->requirement_id = $req->id;
                $cr->status = 0; // pending
                $cr->save(false);
            }
        }
    }

    private static function matches(Organization $org, Requirement $req): bool
    {
        // Check category mask (bit flags: 1=I, 2=II, 4=III, 8=IV)
        $categoryBit = 1 << ($org->category - 1);
        if ($req->category_mask && !($req->category_mask & $categoryBit)) {
            return false;
        }

        // Check water source
        if ($req->need_water && empty($org->water_source)) {
            return false;
        }

        // Check byproduct
        if ($req->need_byproduct && !$org->has_byproduct) {
            return false;
        }

        return true;
    }
}
