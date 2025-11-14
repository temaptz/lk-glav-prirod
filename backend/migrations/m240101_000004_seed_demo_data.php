<?php
use yii\db\Migration;

/**
 * Migration 4: Seed demo data
 * Creates demo users, organizations, and sample data for testing
 */
class m240101_000004_seed_demo_data extends Migration
{
    public function safeUp()
    {
        echo "Seeding demo data...\n\n";
        
        // ==================== DEMO USERS ====================
        $users = [
            ['email' => 'admin@example.com', 'password' => 'admin', 'role' => 'admin'],
            ['email' => 'manager@example.com', 'password' => 'manager', 'role' => 'manager'],
            ['email' => 'specialist@example.com', 'password' => 'specialist', 'role' => 'specialist'],
            ['email' => 'client@example.com', 'password' => 'client', 'role' => 'client'],
        ];

        foreach ($users as $user) {
            $this->insert('auth.users', [
                'email' => $user['email'],
                'password_hash' => password_hash($user['password'], PASSWORD_DEFAULT),
                'role' => $user['role'],
            ]);
            echo "✅ User created: {$user['email']} / {$user['password']} ({$user['role']})\n";
        }

        // Get user IDs
        $adminId = $this->db->createCommand('SELECT id FROM auth.users WHERE email = :email', [':email' => 'admin@example.com'])->queryScalar();
        $managerId = $this->db->createCommand('SELECT id FROM auth.users WHERE email = :email', [':email' => 'manager@example.com'])->queryScalar();
        $specialistId = $this->db->createCommand('SELECT id FROM auth.users WHERE email = :email', [':email' => 'specialist@example.com'])->queryScalar();
        $clientId = $this->db->createCommand('SELECT id FROM auth.users WHERE email = :email', [':email' => 'client@example.com'])->queryScalar();

        echo "\n";

        // ==================== DEMO ORGANIZATIONS ====================
        $organizations = [
            // Organization for CLIENT only
            [
                'name' => 'ООО "Демо Клиент"',
                'inn' => '1234567890',
                'ogrn' => '1234567890123',
                'category' => 2,
                'water_source' => 'скважина',
                'has_byproduct' => false,
                'users' => [$adminId, $clientId], // Only admin and client
            ],
            
            // Organizations for STAFF (admin, manager, specialist)
            [
                'name' => 'ООО "ЭкоПром"',
                'inn' => '7701234567',
                'ogrn' => '1027700123456',
                'category' => 1, // I категория
                'water_source' => 'река',
                'has_byproduct' => true,
                'users' => [$adminId, $managerId, $specialistId],
            ],
            [
                'name' => 'АО "Природа Плюс"',
                'inn' => '7702345678',
                'ogrn' => '1027700234567',
                'category' => 2, // II категория
                'water_source' => 'скважина',
                'has_byproduct' => false,
                'users' => [$adminId, $managerId, $specialistId],
            ],
            [
                'name' => 'ООО "ГринТех"',
                'inn' => '7703456789',
                'ogrn' => '1027700345678',
                'category' => 3, // III категория
                'water_source' => 'река',
                'has_byproduct' => true,
                'users' => [$adminId, $managerId], // No specialist for cat 3
            ],
            [
                'name' => 'ООО "Эко Решения"',
                'inn' => '7705678901',
                'ogrn' => '1027700567890',
                'category' => 2, // II категория
                'water_source' => 'скважина',
                'has_byproduct' => true,
                'users' => [$adminId, $managerId, $specialistId],
            ],
        ];

        foreach ($organizations as $org) {
            $users = $org['users'];
            unset($org['users']);
            
            $this->insert('compliance.organizations', $org);
            $orgId = $this->db->getLastInsertID('compliance.organizations_id_seq');
            
            echo "✅ Organization created: {$org['name']} (category {$org['category']})\n";
            
            // Link users to organization
            foreach ($users as $userId) {
                $this->insert('auth.users_orgs', [
                    'user_id' => $userId,
                    'org_id' => $orgId,
                ]);
            }
            
            // Generate requirements for this organization
            $categoryMask = 1 << ($org['category'] - 1);
            $hasWater = !empty($org['water_source']);
            $hasByproduct = $org['has_byproduct'];
            
            $sql = "
                INSERT INTO compliance.client_requirements (org_id, requirement_id, status, deadline)
                SELECT 
                    :org_id,
                    r.id,
                    0,
                    CURRENT_DATE + INTERVAL '30 days'
                FROM compliance.requirements r
                WHERE (r.category_mask & :category_mask) > 0
                  AND (:has_water = false OR r.need_water = true)
                  AND (:has_byproduct = false OR r.need_byproduct = true)
            ";
            
            $this->db->createCommand($sql, [
                ':org_id' => $orgId,
                ':category_mask' => $categoryMask,
                ':has_water' => $hasWater,
                ':has_byproduct' => $hasByproduct,
            ])->execute();
            
            $reqCount = $this->db->createCommand(
                'SELECT COUNT(*) FROM compliance.client_requirements WHERE org_id = :org_id',
                [':org_id' => $orgId]
            )->queryScalar();
            echo "   → {$reqCount} requirements generated\n";
            
            // Create calendar events for requirements
            $requirements = $this->db->createCommand(
                'SELECT requirement_id, deadline FROM compliance.client_requirements WHERE org_id = :org_id LIMIT 5',
                [':org_id' => $orgId]
            )->queryAll();
            
            foreach ($requirements as $req) {
                $this->insert('compliance.calendar_events', [
                    'org_id' => $orgId,
                    'requirement_id' => $req['requirement_id'],
                    'title' => 'Срок сдачи отчётности',
                    'event_date' => $req['deadline'],
                ]);
            }
            echo "   → " . count($requirements) . " calendar events created\n";
        }

        echo "\n";

        // ==================== DEMO FINANCE DATA ====================
        $orgIds = $this->db->createCommand('SELECT id, name FROM compliance.organizations')->queryAll();
        $contractCounter = 1;
        $currentYear = date('Y');

        foreach ($orgIds as $org) {
            $orgId = $org['id'];
            $orgName = $org['name'];
            
            // Create 1-2 contracts per organization
            $numContracts = rand(1, 2);
            
            for ($i = 0; $i < $numContracts; $i++) {
                $contractNum = sprintf('%03d/%s', $contractCounter++, $currentYear);
                $startDate = date('Y-m-d', strtotime("-" . rand(30, 180) . " days"));
                
                $this->insert('finance.contracts', [
                    'org_id' => $orgId,
                    'number' => $contractNum,
                    'signed_at' => $startDate,
                    'status' => 'active',
                ]);
                
                $contractId = $this->db->getLastInsertID('finance.contracts_id_seq');
                
                // Create 2-4 invoices
                $numInvoices = rand(2, 4);
                for ($j = 1; $j <= $numInvoices; $j++) {
                    $invoiceNum = sprintf('СЧ-%s-%d', $contractNum, $j);
                    $invoiceDate = date('Y-m-d', strtotime($startDate . " +{$j} months"));
                    $isPaid = rand(0, 100) > 30; // 70% paid
                    
                    $this->insert('finance.invoices', [
                        'contract_id' => $contractId,
                        'number' => $invoiceNum,
                        'amount' => rand(10000, 50000),
                        'issued_at' => $invoiceDate,
                        'paid_at' => $isPaid ? date('Y-m-d', strtotime($invoiceDate . " +" . rand(1, 15) . " days")) : null,
                        'status' => $isPaid ? 'paid' : 'pending',
                    ]);
                }
                
                // Create 1-3 acts
                $numActs = rand(1, 3);
                for ($k = 1; $k <= $numActs; $k++) {
                    $actNum = sprintf('АКТ-%s-%d', $contractNum, $k);
                    $actDate = date('Y-m-d', strtotime($startDate . " +{$k} months"));
                    $isAccepted = rand(0, 100) > 20; // 80% accepted
                    
                    $this->insert('finance.acts', [
                        'contract_id' => $contractId,
                        'number' => $actNum,
                        'accepted_at' => $isAccepted ? date('Y-m-d', strtotime($actDate . " +" . rand(3, 20) . " days")) : null,
                        'status' => $isAccepted ? 'accepted' : 'draft',
                    ]);
                }
                
                echo "✅ Contract {$contractNum} for {$orgName}: {$numInvoices} invoices, {$numActs} acts\n";
            }
        }

        echo "\n🎉 Demo data seeded successfully!\n\n";
        echo "=== ACCESS SUMMARY ===\n";
        echo "👤 admin@example.com / admin → 5 organizations (all)\n";
        echo "👤 manager@example.com / manager → 4 organizations (categories 1-3)\n";
        echo "👤 specialist@example.com / specialist → 3 organizations (categories 1-2)\n";
        echo "👤 client@example.com / client → 1 organization (ООО \"Демо Клиент\" only)\n";
    }

    public function safeDown()
    {
        // Delete in reverse order
        $this->delete('finance.acts');
        $this->delete('finance.invoices');
        $this->delete('finance.contracts');
        $this->delete('compliance.calendar_events');
        $this->delete('compliance.artifacts');
        $this->delete('compliance.client_requirements');
        $this->delete('auth.users_orgs');
        $this->delete('compliance.organizations');
        $this->delete('auth.users');
    }
}
