<?php
use yii\db\Migration;

/**
 * Migration 2: Configure Row-Level Security (RLS)
 * Sets up RLS policies for multi-tenant data isolation
 */
class m240101_000002_configure_rls extends Migration
{
    public function safeUp()
    {
        echo "Configuring RLS policies...\n\n";
        
        // ==================== ORGANIZATIONS ====================
        $this->execute("ALTER TABLE compliance.organizations ENABLE ROW LEVEL SECURITY");
        $this->execute("
            CREATE POLICY org_rls ON compliance.organizations
            FOR ALL
            USING (
                id IN (
                    SELECT org_id 
                    FROM auth.users_orgs 
                    WHERE user_id = current_setting('app.user_id', true)::int
                )
            )
        ");
        echo "✅ RLS enabled on compliance.organizations\n";

        // ==================== CLIENT REQUIREMENTS ====================
        $this->execute("ALTER TABLE compliance.client_requirements ENABLE ROW LEVEL SECURITY");
        $this->execute("
            CREATE POLICY client_requirements_rls ON compliance.client_requirements
            FOR ALL
            USING (
                org_id IN (
                    SELECT org_id 
                    FROM auth.users_orgs 
                    WHERE user_id = current_setting('app.user_id', true)::int
                )
            )
        ");
        echo "✅ RLS enabled on compliance.client_requirements\n";

        // ==================== ARTIFACTS ====================
        $this->execute("ALTER TABLE compliance.artifacts ENABLE ROW LEVEL SECURITY");
        $this->execute("
            CREATE POLICY artifacts_rls ON compliance.artifacts
            FOR ALL
            USING (
                org_id IN (
                    SELECT org_id 
                    FROM auth.users_orgs 
                    WHERE user_id = current_setting('app.user_id', true)::int
                )
            )
        ");
        echo "✅ RLS enabled on compliance.artifacts\n";

        // ==================== CALENDAR EVENTS ====================
        $this->execute("ALTER TABLE compliance.calendar_events ENABLE ROW LEVEL SECURITY");
        $this->execute("
            CREATE POLICY calendar_events_rls ON compliance.calendar_events
            FOR ALL
            USING (
                org_id IN (
                    SELECT org_id 
                    FROM auth.users_orgs 
                    WHERE user_id = current_setting('app.user_id', true)::int
                )
            )
        ");
        echo "✅ RLS enabled on compliance.calendar_events\n";

        // ==================== CONTRACTS ====================
        $this->execute("ALTER TABLE finance.contracts ENABLE ROW LEVEL SECURITY");
        $this->execute("
            CREATE POLICY contracts_rls ON finance.contracts
            FOR ALL
            USING (
                org_id IN (
                    SELECT org_id 
                    FROM auth.users_orgs 
                    WHERE user_id = current_setting('app.user_id', true)::int
                )
            )
        ");
        echo "✅ RLS enabled on finance.contracts\n";

        // ==================== INVOICES ====================
        $this->execute("ALTER TABLE finance.invoices ENABLE ROW LEVEL SECURITY");
        $this->execute("
            CREATE POLICY invoices_rls ON finance.invoices
            FOR ALL
            USING (
                contract_id IN (
                    SELECT c.id 
                    FROM finance.contracts c
                    INNER JOIN auth.users_orgs uo ON uo.org_id = c.org_id
                    WHERE uo.user_id = current_setting('app.user_id', true)::int
                )
            )
        ");
        echo "✅ RLS enabled on finance.invoices\n";

        // ==================== ACTS ====================
        $this->execute("ALTER TABLE finance.acts ENABLE ROW LEVEL SECURITY");
        $this->execute("
            CREATE POLICY acts_rls ON finance.acts
            FOR ALL
            USING (
                contract_id IN (
                    SELECT c.id 
                    FROM finance.contracts c
                    INNER JOIN auth.users_orgs uo ON uo.org_id = c.org_id
                    WHERE uo.user_id = current_setting('app.user_id', true)::int
                )
            )
        ");
        echo "✅ RLS enabled on finance.acts\n";

        echo "\n🎉 RLS policies configured successfully!\n";
        echo "All tables are now protected with row-level security.\n";
    }

    public function safeDown()
    {
        $this->execute("DROP POLICY IF EXISTS acts_rls ON finance.acts");
        $this->execute("DROP POLICY IF EXISTS invoices_rls ON finance.invoices");
        $this->execute("DROP POLICY IF EXISTS contracts_rls ON finance.contracts");
        $this->execute("DROP POLICY IF EXISTS calendar_events_rls ON compliance.calendar_events");
        $this->execute("DROP POLICY IF EXISTS artifacts_rls ON compliance.artifacts");
        $this->execute("DROP POLICY IF EXISTS client_requirements_rls ON compliance.client_requirements");
        $this->execute("DROP POLICY IF EXISTS org_rls ON compliance.organizations");
        
        $this->execute("ALTER TABLE finance.acts DISABLE ROW LEVEL SECURITY");
        $this->execute("ALTER TABLE finance.invoices DISABLE ROW LEVEL SECURITY");
        $this->execute("ALTER TABLE finance.contracts DISABLE ROW LEVEL SECURITY");
        $this->execute("ALTER TABLE compliance.calendar_events DISABLE ROW LEVEL SECURITY");
        $this->execute("ALTER TABLE compliance.artifacts DISABLE ROW LEVEL SECURITY");
        $this->execute("ALTER TABLE compliance.client_requirements DISABLE ROW LEVEL SECURITY");
        $this->execute("ALTER TABLE compliance.organizations DISABLE ROW LEVEL SECURITY");
    }
}
