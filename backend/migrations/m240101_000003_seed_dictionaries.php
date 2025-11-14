<?php
use yii\db\Migration;

/**
 * Migration 3: Seed dictionaries (справочники)
 * Loads requirements and risks reference data
 */
class m240101_000003_seed_dictionaries extends Migration
{
    public function safeUp()
    {
        echo "Seeding dictionaries...\n\n";
        
        // ==================== REQUIREMENTS ====================
        $requirements = [
            // Category I-IV (all) - mask 15 = 0b1111
            ['code' => 'REQ-001', 'title' => 'Журналы учета движения отходов производства и потребления', 'category_mask' => 15, 'need_water' => false, 'need_byproduct' => false],
            ['code' => 'REQ-003', 'title' => 'Статотчетность по форме 2-ТП (воздух), при условии суммарного выброса более 5 тонн/год', 'category_mask' => 15, 'need_water' => false, 'need_byproduct' => false],
            ['code' => 'REQ-004', 'title' => 'Статотчетность по форме 2-ТП (отходы), при условии образования отходов более 100 кг', 'category_mask' => 15, 'need_water' => false, 'need_byproduct' => false],
            
            // Category I-III (not IV) - mask 7 = 0b0111
            ['code' => 'REQ-002', 'title' => 'Журналы учета стационарных источников выбросов и их характеристик', 'category_mask' => 7, 'need_water' => false, 'need_byproduct' => false],
            ['code' => 'REQ-005', 'title' => 'Декларация о плате за негативное воздействие на окружающую среду', 'category_mask' => 7, 'need_water' => false, 'need_byproduct' => false],
            ['code' => 'REQ-006', 'title' => 'Отчет по программе производственного экологического контроля (ПЭК)', 'category_mask' => 7, 'need_water' => false, 'need_byproduct' => false],
            
            // Category I-II only - mask 3 = 0b0011
            ['code' => 'REQ-012', 'title' => 'Нормативы образования отходов и лимиты на их размещение (НООЛР)', 'category_mask' => 3, 'need_water' => false, 'need_byproduct' => false],
            ['code' => 'REQ-013', 'title' => 'Нормативы допустимых выбросов (НДВ)', 'category_mask' => 3, 'need_water' => false, 'need_byproduct' => false],
            
            // Category III only - mask 4 = 0b0100
            ['code' => 'REQ-014', 'title' => 'Нормативы допустимых выбросов для радиоактивных, высокотоксичных веществ', 'category_mask' => 4, 'need_water' => false, 'need_byproduct' => false],
            
            // Category I only - mask 1 = 0b0001
            ['code' => 'REQ-020', 'title' => 'Комплексное экологическое разрешение (КЭР) для объектов I категории', 'category_mask' => 1, 'need_water' => false, 'need_byproduct' => false],
            
            // Category II only - mask 2 = 0b0010
            ['code' => 'REQ-019', 'title' => 'Декларация о воздействии на окружающую среду (ДВОС) для объектов II категории', 'category_mask' => 2, 'need_water' => false, 'need_byproduct' => false],
            
            // Water source requirements
            ['code' => 'REQ-W01', 'title' => 'Лицензия на право пользования недрами', 'category_mask' => 15, 'need_water' => true, 'need_byproduct' => false, 'npa_ref' => 'Скважина'],
            ['code' => 'REQ-W02', 'title' => 'Решение на право пользования водным объектом и/или Договор водопользования', 'category_mask' => 15, 'need_water' => true, 'need_byproduct' => false, 'npa_ref' => 'Река/озеро'],
            
            // Byproduct
            ['code' => 'REQ-BP01', 'title' => 'Технические условия "Удобрения органические на основе побочной продукции животноводства"', 'category_mask' => 15, 'need_water' => false, 'need_byproduct' => true],
        ];

        foreach ($requirements as $req) {
            $this->insert('compliance.requirements', $req);
        }
        echo "✅ " . count($requirements) . " requirements inserted\n";

        // ==================== RISKS ====================
        $risks = [
            ['code' => 'RISK-001', 'title' => 'Штрафы и санкции за несвоевременную сдачу отчетности', 'description' => 'Административная ответственность по ст. 8.5 КоАП РФ', 'severity' => 4],
            ['code' => 'RISK-002', 'title' => 'Приостановление деятельности', 'description' => 'Возможна приостановка деятельности организации при критических нарушениях', 'severity' => 5],
            ['code' => 'RISK-003', 'title' => 'Репутационные риски', 'description' => 'Ущерб деловой репутации при выявлении экологических нарушений', 'severity' => 3],
            ['code' => 'RISK-004', 'title' => 'Финансовые потери из-за просрочки платежей', 'description' => 'Пени и дополнительные платежи за просрочку экологических платежей', 'severity' => 3],
        ];

        foreach ($risks as $risk) {
            $this->insert('compliance.risks', $risk);
        }
        echo "✅ " . count($risks) . " risks inserted\n";

        echo "\n🎉 Dictionaries seeded successfully!\n";
    }

    public function safeDown()
    {
        $this->delete('compliance.requirement_risks');
        $this->delete('compliance.risks');
        $this->delete('compliance.requirements');
    }
}
