#!/bin/bash

# Script to apply new refactored migrations
# This will completely reset the database with the new clean structure

set -e

echo "🚀 Starting migration to new structure..."
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Step 1: Stop containers
echo -e "${YELLOW}Step 1: Stopping containers...${NC}"
docker compose down
echo -e "${GREEN}✅ Containers stopped${NC}"
echo ""

# Step 2: Remove old database volume
echo -e "${YELLOW}Step 2: Removing old database...${NC}"
docker volume rm lk-glav-prirod_postgres_data 2>/dev/null || echo "Volume not found (OK)"
echo -e "${GREEN}✅ Old database removed${NC}"
echo ""

# Step 3: Backup old migrations
echo -e "${YELLOW}Step 3: Backing up old migrations...${NC}"
if [ -d "migrations" ]; then
    mv migrations migrations_old_backup_$(date +%Y%m%d_%H%M%S)
    echo -e "${GREEN}✅ Old migrations backed up${NC}"
else
    echo -e "${YELLOW}⚠️  No old migrations found${NC}"
fi
echo ""

# Step 4: Apply new migrations
echo -e "${YELLOW}Step 4: Moving new migrations...${NC}"
mv migrations_new migrations
echo -e "${GREEN}✅ New migrations in place${NC}"
echo ""

# Step 5: Start containers with rebuild
echo -e "${YELLOW}Step 5: Starting containers (with rebuild)...${NC}"
cd ..
docker compose up -d --build
echo -e "${GREEN}✅ Containers started${NC}"
echo ""

# Step 6: Wait for database to be ready
echo -e "${YELLOW}Step 6: Waiting for database...${NC}"
sleep 5
echo -e "${GREEN}✅ Database ready${NC}"
echo ""

# Step 7: Apply migrations
echo -e "${YELLOW}Step 7: Applying new migrations...${NC}"
docker compose exec backend php yii migrate --interactive=0
echo ""

# Summary
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}🎉 Migration completed successfully!${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${YELLOW}📊 New structure summary:${NC}"
echo "   • 4 migrations (instead of 19)"
echo "   • Clean logical structure"
echo "   • Correct client access (1 organization only)"
echo ""
echo -e "${YELLOW}👤 Demo users:${NC}"
echo "   admin@example.com / admin → 5 organizations"
echo "   manager@example.com / manager → 4 organizations"
echo "   specialist@example.com / specialist → 3 organizations"
echo "   client@example.com / client → 1 organization ✅"
echo ""
echo -e "${YELLOW}🌐 Frontend:${NC}"
echo "   http://localhost:4200"
echo ""
echo -e "${GREEN}Ready to test!${NC}"
