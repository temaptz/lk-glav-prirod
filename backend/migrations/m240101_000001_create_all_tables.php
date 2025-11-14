<?php
use yii\db\Migration;

/**
 * Migration 1: Create all database schemas and tables
 * Creates auth, compliance, finance, and audit schemas with all necessary tables
 */
class m240101_000001_create_all_tables extends Migration
{
    public function safeUp()
    {
        // ==================== SCHEMAS ====================
        $this->execute("CREATE SCHEMA IF NOT EXISTS auth");
        $this->execute("CREATE SCHEMA IF NOT EXISTS compliance");
        $this->execute("CREATE SCHEMA IF NOT EXISTS finance");
        $this->execute("CREATE SCHEMA IF NOT EXISTS audit");
        
        echo "✅ Schemas created\n";

        // ==================== AUTH SCHEMA ====================
        
        // Users table
        $this->createTable('auth.users', [
            'id' => $this->primaryKey(),
            'email' => $this->string()->notNull()->unique(),
            'password_hash' => $this->string()->notNull(),
            'role' => $this->string(20)->notNull()->defaultValue('client'),
            'created_at' => $this->timestamp()->defaultExpression('NOW()'),
        ]);
        
        // Users-Organizations mapping table (many-to-many)
        $this->createTable('auth.users_orgs', [
            'user_id' => $this->integer()->notNull(),
            'org_id' => $this->integer()->notNull(),
        ]);
        $this->addPrimaryKey('pk_users_orgs', 'auth.users_orgs', ['user_id', 'org_id']);
        
        echo "✅ Auth tables created\n";

        // ==================== COMPLIANCE SCHEMA ====================
        
        // Organizations
        $this->createTable('compliance.organizations', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'inn' => $this->string(12)->notNull()->unique(),
            'ogrn' => $this->string(15)->notNull(),
            'category' => $this->smallInteger()->notNull(), // 1-4 (НВОС категория)
            'water_source' => $this->string(20)->null(),
            'has_byproduct' => $this->boolean()->defaultValue(false),
            'created_at' => $this->timestamp()->defaultExpression('NOW()'),
        ]);
        
        // Requirements dictionary (справочник требований)
        $this->createTable('compliance.requirements', [
            'id' => $this->primaryKey(),
            'code' => $this->string()->notNull()->unique(),
            'title' => $this->string()->notNull(),
            'npa_ref' => $this->string()->null(),
            'category_mask' => $this->integer()->notNull()->defaultValue(0), // битовая маска категорий
            'need_water' => $this->boolean()->notNull()->defaultValue(false),
            'need_byproduct' => $this->boolean()->notNull()->defaultValue(false),
        ]);
        
        // Client requirements (требования для конкретного клиента)
        $this->createTable('compliance.client_requirements', [
            'id' => $this->primaryKey(),
            'org_id' => $this->integer()->notNull(),
            'requirement_id' => $this->integer()->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0), // 0=pending, 1=in_progress, 2=done
            'deadline' => $this->date()->null(),
            'responsible_user_id' => $this->integer()->null(),
        ]);
        
        // Artifacts (документы)
        $this->createTable('compliance.artifacts', [
            'id' => $this->primaryKey(),
            'org_id' => $this->integer()->notNull(),
            'requirement_id' => $this->integer()->null(),
            'path' => $this->string()->notNull(),
            'filename' => $this->string()->null(),
            'original_name' => $this->string()->null(),
            'mime' => $this->string(100)->null(),
            'with_audit' => $this->boolean()->defaultValue(false),
            'uploaded_at' => $this->timestamp()->defaultExpression('NOW()'),
            'uploaded_by' => $this->integer()->null(),
        ]);
        
        // Calendar events
        $this->createTable('compliance.calendar_events', [
            'id' => $this->primaryKey(),
            'org_id' => $this->integer()->notNull(),
            'title' => $this->string()->notNull(),
            'event_date' => $this->date()->notNull(),
            'requirement_id' => $this->integer()->null(),
            'created_at' => $this->timestamp()->defaultExpression('NOW()'),
        ]);
        
        // Risks dictionary (справочник рисков)
        $this->createTable('compliance.risks', [
            'id' => $this->primaryKey(),
            'code' => $this->string()->notNull()->unique(),
            'title' => $this->string()->notNull(),
            'description' => $this->text()->null(),
            'severity' => $this->smallInteger()->defaultValue(1), // 1-5
        ]);
        
        // Requirement-Risk mapping (many-to-many)
        $this->createTable('compliance.requirement_risks', [
            'requirement_id' => $this->integer()->notNull(),
            'risk_id' => $this->integer()->notNull(),
        ]);
        $this->addPrimaryKey('pk_req_risk', 'compliance.requirement_risks', ['requirement_id', 'risk_id']);
        
        echo "✅ Compliance tables created\n";

        // ==================== FINANCE SCHEMA ====================
        
        // Contracts
        $this->createTable('finance.contracts', [
            'id' => $this->primaryKey(),
            'org_id' => $this->integer()->notNull(),
            'number' => $this->string()->notNull(),
            'signed_at' => $this->date()->null(),
            'status' => $this->string(20)->notNull()->defaultValue('active'),
        ]);
        
        // Invoices
        $this->createTable('finance.invoices', [
            'id' => $this->primaryKey(),
            'contract_id' => $this->integer()->notNull(),
            'number' => $this->string()->notNull(),
            'amount' => $this->decimal(12, 2)->notNull(),
            'issued_at' => $this->date()->notNull(),
            'paid_at' => $this->date()->null(),
            'status' => $this->string(20)->defaultValue('pending'),
        ]);
        
        // Acts
        $this->createTable('finance.acts', [
            'id' => $this->primaryKey(),
            'contract_id' => $this->integer()->notNull(),
            'number' => $this->string()->notNull(),
            'accepted_at' => $this->date()->null(),
            'status' => $this->string(20)->defaultValue('draft'),
        ]);
        
        echo "✅ Finance tables created\n";

        // ==================== AUDIT SCHEMA ====================
        
        // Audit logs (152-ФЗ compliance)
        $this->createTable('audit.logs', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'action' => $this->string(50)->notNull(),
            'model' => $this->string(100)->notNull(),
            'model_id' => $this->integer()->null(),
            'old_value' => $this->text()->null(),
            'new_value' => $this->text()->null(),
            'ip_address' => $this->string(45)->null(),
            'created_at' => $this->timestamp()->defaultExpression('NOW()'),
        ]);
        $this->createIndex('idx_audit_user', 'audit.logs', 'user_id');
        $this->createIndex('idx_audit_action', 'audit.logs', 'action');
        $this->createIndex('idx_audit_created', 'audit.logs', 'created_at');
        
        echo "✅ Audit tables created\n";

        // ==================== FOREIGN KEYS ====================
        
        // Auth
        $this->addForeignKey('fk_uo_user', 'auth.users_orgs', 'user_id', 'auth.users', 'id', 'CASCADE');
        $this->addForeignKey('fk_uo_org', 'auth.users_orgs', 'org_id', 'compliance.organizations', 'id', 'CASCADE');
        
        // Compliance
        $this->addForeignKey('fk_client_req_org', 'compliance.client_requirements', 'org_id', 'compliance.organizations', 'id', 'CASCADE');
        $this->addForeignKey('fk_client_req_req', 'compliance.client_requirements', 'requirement_id', 'compliance.requirements', 'id', 'CASCADE');
        $this->addForeignKey('fk_artifacts_org', 'compliance.artifacts', 'org_id', 'compliance.organizations', 'id', 'CASCADE');
        $this->addForeignKey('fk_artifact_requirement', 'compliance.artifacts', 'requirement_id', 'compliance.requirements', 'id', 'SET NULL');
        $this->addForeignKey('fk_cal_org', 'compliance.calendar_events', 'org_id', 'compliance.organizations', 'id', 'CASCADE');
        $this->addForeignKey('fk_cal_req', 'compliance.calendar_events', 'requirement_id', 'compliance.requirements', 'id', 'SET NULL');
        $this->addForeignKey('fk_rr_req', 'compliance.requirement_risks', 'requirement_id', 'compliance.requirements', 'id', 'CASCADE');
        $this->addForeignKey('fk_rr_risk', 'compliance.requirement_risks', 'risk_id', 'compliance.risks', 'id', 'CASCADE');
        
        // Finance
        $this->addForeignKey('fk_contracts_org', 'finance.contracts', 'org_id', 'compliance.organizations', 'id', 'CASCADE');
        $this->addForeignKey('fk_inv_contract', 'finance.invoices', 'contract_id', 'finance.contracts', 'id', 'CASCADE');
        $this->addForeignKey('fk_act_contract', 'finance.acts', 'contract_id', 'finance.contracts', 'id', 'CASCADE');
        
        // Audit
        $this->addForeignKey('fk_audit_user', 'audit.logs', 'user_id', 'auth.users', 'id', 'CASCADE');
        
        echo "✅ Foreign keys created\n";
        echo "\n🎉 All tables created successfully!\n";
    }

    public function safeDown()
    {
        // Drop in reverse order
        $this->dropTable('audit.logs');
        $this->dropTable('finance.acts');
        $this->dropTable('finance.invoices');
        $this->dropTable('finance.contracts');
        $this->dropTable('compliance.requirement_risks');
        $this->dropTable('compliance.risks');
        $this->dropTable('compliance.calendar_events');
        $this->dropTable('compliance.artifacts');
        $this->dropTable('compliance.client_requirements');
        $this->dropTable('compliance.requirements');
        $this->dropTable('compliance.organizations');
        $this->dropTable('auth.users_orgs');
        $this->dropTable('auth.users');
        
        $this->execute("DROP SCHEMA IF EXISTS audit CASCADE");
        $this->execute("DROP SCHEMA IF EXISTS finance CASCADE");
        $this->execute("DROP SCHEMA IF EXISTS compliance CASCADE");
        $this->execute("DROP SCHEMA IF EXISTS auth CASCADE");
    }
}
