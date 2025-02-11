#!/bin/bash

# Configuration
BASE_URL="http://localhost:8000"
USER1_EMAIL="user1@test.com"
USER1_PASSWORD="password"
USER2_EMAIL="user2@test.com"
USER2_PASSWORD="password"
ADMIN_EMAIL="admin@test.com"
ADMIN_PASSWORD="password"

# Fonction pour obtenir le token CSRF
get_csrf_token() {
    local response=$(curl -s -c cookies.txt -b cookies.txt "$BASE_URL/login")
    echo "$response" | grep -o 'value="[^"]*"' | head -1 | cut -d'"' -f2
}

# Fonction pour se connecter
login() {
    local email=$1
    local password=$2
    local csrf_token=$(get_csrf_token)
    
    curl -s -c cookies.txt -b cookies.txt -X POST "$BASE_URL/login" \
        -H "Content-Type: application/x-www-form-urlencoded" \
        -d "_csrf_token=$csrf_token" \
        -d "_username=$email" \
        -d "_password=$password"
}

# Test 1: Créer une tâche par défaut
echo "Test 1: Création d'une tâche par défaut"
login "$ADMIN_EMAIL" "$ADMIN_PASSWORD"
CHANNEL_ID=1
curl -s -c cookies.txt -b cookies.txt -X POST "$BASE_URL/admin/channels/$CHANNEL_ID/tasks" \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -d "task[title]=Default Task" \
    -d "task[description]=This is a default task" \
    -d "task[isDefault]=1"

# Test 2: Vérifier que l'utilisateur 1 peut voir la tâche
echo -e "\nTest 2: Utilisateur 1 peut voir la tâche"
login "$USER1_EMAIL" "$USER1_PASSWORD"
curl -s -c cookies.txt -b cookies.txt "$BASE_URL/web/channels/$CHANNEL_ID/tasks"

# Test 3: Archiver la tâche en tant qu'utilisateur 1
echo -e "\nTest 3: Archivage de la tâche par l'utilisateur 1"
TASK_ID=1
csrf_token=$(get_csrf_token)
curl -s -c cookies.txt -b cookies.txt -X POST "$BASE_URL/web/channels/$CHANNEL_ID/tasks/$TASK_ID" \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -d "_token=$csrf_token"

# Test 4: Vérifier que l'utilisateur 1 ne voit plus la tâche
echo -e "\nTest 4: Utilisateur 1 ne devrait plus voir la tâche"
curl -s -c cookies.txt -b cookies.txt "$BASE_URL/web/channels/$CHANNEL_ID/tasks"

# Test 5: Vérifier que l'utilisateur 2 voit toujours la tâche
echo -e "\nTest 5: Utilisateur 2 devrait voir la tâche"
login "$USER2_EMAIL" "$USER2_PASSWORD"
curl -s -c cookies.txt -b cookies.txt "$BASE_URL/web/channels/$CHANNEL_ID/tasks"

# Test 6: Vérifier que l'admin voit la tâche
echo -e "\nTest 6: Admin devrait voir la tâche"
login "$ADMIN_EMAIL" "$ADMIN_PASSWORD"
curl -s -c cookies.txt -b cookies.txt "$BASE_URL/web/channels/$CHANNEL_ID/tasks"

# Nettoyage
rm -f cookies.txt
