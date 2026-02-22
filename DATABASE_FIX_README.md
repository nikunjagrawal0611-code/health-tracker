# Database Connection Fix - Health Tracker API

## 🔧 What I Fixed

### 1. **Enhanced Error Handling in database.php**
   - Added detailed error messages with host, database, and port info
   - Added error logging capability
   - Added proper HTTP status codes for connection failures
   - Added timezone configuration

### 2. **Improved API Files with Better Error Handling**
   - **login.php**: Added input validation, error checking for statement preparation and execution
   - **register.php**: Added email validation, better error messages, proper HTTP status codes
   - **food_entries.php**: 
     - Removed duplicate `session_start()` call
     - Added null checking for prepared statements
     - Fixed SQL injection vulnerability in pagination query
     - Proper error responses and status codes
   - **goals.php**: Added error checking for all database operations
   - **reports.php**: Added error checking for prepared statements and execution
   - **logout.php**: Added proper response headers and messages

### 3. **Created Database Setup Tools**
   - **setup.php**: Automated script to create database and all tables
   - **test_connection.php**: Diagnostic tool to verify database connection

## ✅ Setup Instructions

### Step 1: Run Database Setup
1. Make sure MySQL/MariaDB is running
2. Visit: `http://localhost/health_tracker/api/setup.php`
3. You should see ✅ checkmarks for all setup items
4. If you see ❌, check the errors and your MySQL credentials

### Step 2: Verify Connection
Visit: `http://localhost/health_tracker/api/test_connection.php`

You should see JSON output like:
```json
{
  "status": "success",
  "checks": {
    "mysqli_extension": "✅ Pass",
    "mysql_connection": "✅ Connected to MySQL",
    "database_selection": "✅ Database 'health_tracker' exists",
    "table_users": "✅ Table exists",
    "table_food_entries": "✅ Table exists",
    "table_goals": "✅ Table exists",
    "charset": "✅ Charset OK"
  }
}
```

## 🔐 Database Credentials (in database.php)

Current credentials:
```
Host: localhost (or 127.0.0.1)
Database: health_tracker
User: root
Password: (empty)
Port: 3306
```

If your credentials are different, update `database.php`:
```php
$host = "your_host";
$db = "your_database";
$user = "your_username";
$pass = "your_password";
$port = 3306;
```

## 🗄️ Database Structure

The setup creates 3 tables:

### `users` table
- id (Primary Key)
- name, email, password_hash
- created_at timestamp

### `food_entries` table
- id (Primary Key)
- user_id (Foreign Key to users)
- meal_type, food_name, quantity
- calories, protein, carbs, fat
- entry_date
- created_at timestamp

### `goals` table
- id (Primary Key)
- user_id (Unique, Foreign Key to users)
- daily_calories, protein_target, carbs_target, fat_target
- weight_goal
- created_at/updated_at timestamps

## 🐛 Troubleshooting

### "Connection refused"
- Check if MySQL/MariaDB is running
- Verify host and port are correct
- Try: `telnet localhost 3306`

### "Access denied"
- Verify username and password in database.php
- Check MySQL user privileges

### "Database doesn't exist"
- Run setup.php: `http://localhost/health_tracker/api/setup.php`

### "Table doesn't exist"
- Run setup.php again
- Check error messages for table creation failures

### 404 errors on API calls
- Verify correct file paths
- Check that `.php` files exist in `/api/` folder

## 📝 Testing the API

Once connected, test with curl:

**Register:**
```bash
curl -X POST http://localhost/health_tracker/api/register.php \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com","password":"pass123"}'
```

**Login:**
```bash
curl -c cookies.txt -X POST http://localhost/health_tracker/api/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"pass123"}'
```

**Add Food Entry:**
```bash
curl -b cookies.txt -X POST http://localhost/health_tracker/api/food_entries.php \
  -H "Content-Type: application/json" \
  -d '{"meal_type":"breakfast","food_name":"Eggs","calories":155,"protein":13,"carbs":1,"fat":11,"entry_date":"2026-02-22"}'
```

## 🗑️ Cleanup

After setup is confirmed working:
- You can delete `setup.php` (not needed anymore)
- Keep `test_connection.php` for future diagnostics
- Or delete both if you prefer

## 📧 Need Help?

If issues persist:
1. Run `test_connection.php` and share the output
2. Check your MySQL error log
3. Verify MySQL is running: `mysql -u root -p`
4. Run: `SHOW DATABASES;` to verify health_tracker exists