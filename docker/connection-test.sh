#!/bin/bash

# Connection Test Script for External Services

echo "=== Testing PostgreSQL 17 Connection ==="
echo "Host: 192.168.0.4:5432"
echo "Database: default_db"
echo "User: gen_user"

# Test PostgreSQL connection
docker run --rm -it postgres:17-alpine psql 'postgresql://gen_user:X3wbvNWxCtT4B%24@192.168.0.4:5432/default_db' -c "SELECT version();"

if [ $? -eq 0 ]; then
    echo "✅ PostgreSQL connection successful"
else
    echo "❌ PostgreSQL connection failed"
fi

echo ""
echo "=== Testing Redis 8.1 Connection ==="
echo "Host: 192.168.0.5:6379"
echo "User: default"

# Test Redis connection
docker run --rm -it redis:7-alpine redis-cli -h 192.168.0.5 -p 6379 --user default --pass '?:W3K@aXg(0D!@' ping

if [ $? -eq 0 ]; then
    echo "✅ Redis connection successful"
else
    echo "❌ Redis connection failed"
fi

echo ""
echo "=== Database Schema Check ==="
docker run --rm -it postgres:17-alpine psql 'postgresql://gen_user:X3wbvNWxCtT4B%24@192.168.0.4:5432/default_db' -c "SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = 'public';"

echo ""
echo "=== Redis Info ==="
docker run --rm -it redis:7-alpine redis-cli -h 192.168.0.5 -p 6379 --user default --pass '?:W3K@aXg(0D!@' info server
