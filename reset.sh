docker-compose rm -svf database
docker-compose up -d --build database
echo "waiting for database..."
sleep 15
docker-compose restart backend
