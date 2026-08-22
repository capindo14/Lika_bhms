<?php
/**
 * Barangay Health Monitoring System - Database Setup & Seeder Script
 * Run this script to create the database, tables, and seed them with dummy data.
 */

// Define database parameters matching config.php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'barangay_health');

echo "===========================================\n";
echo "Barangay Health Monitoring System DB Setup\n";
echo "===========================================\n\n";

try {
    // 1. Establish connection to MySQL server without database selected
    echo "Connecting to MySQL server at " . DB_HOST . "... ";
    $db = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    echo "Connected!\n";
    $db->exec("DROP DATABASE IF EXISTS `" . DB_NAME . "`");

    // 2. Read and parse schema.sql
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Error: schema.sql file not found in " . __DIR__);
    }
    
    echo "Parsing database schema file... ";
    $sqlContent = file_get_contents($schemaFile);
    
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
    
    // Split queries by semicolon to execute one by one
    // Simple parser
    $queries = preg_split('/;(?=(?:[^\'"]*[\'"][^\'"]*[\'"])*[^\'"]*$)/', $sqlContent);
    echo "Done! (" . count($queries) . " queries found)\n";

    echo "Creating database structure:\n";
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;
        
        // Print snippet of query for tracing
        $snippet = substr($query, 0, 50) . "...";
        echo " -> Executing: {$snippet} ";
        $db->exec($query);
        echo "SUCCESS\n";
    }
    echo "\nDatabase and tables created successfully!\n\n";

    // Re-establish connection specifically selecting our database
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 3. Seeding Data
    echo "===========================================\n";
    echo "Seeding System Accounts...\n";
    echo "===========================================\n";
    
    $users = [
        [
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'Admin',
            'fullname' => 'System Administrator'
        ],
        [
            'username' => 'worker',
            'password' => password_hash('worker123', PASSWORD_DEFAULT),
            'role' => 'Health Worker',
            'fullname' => 'Dr. Clarissa Santos, MD'
        ],
        [
            'username' => 'staff',
            'password' => password_hash('staff123', PASSWORD_DEFAULT),
            'role' => 'Staff',
            'fullname' => 'Jessica Gomez'
        ]
    ];

    $stmtUser = $db->prepare("
        INSERT INTO users (username, password, role, fullname, status)
        VALUES (:username, :password, :role, :fullname, 'Active')
    ");

    foreach ($users as $u) {
        $stmtUser->execute([
            ':username' => $u['username'],
            ':password' => $u['password'],
            ':role' => $u['role'],
            ':fullname' => $u['fullname']
        ]);
        echo " -> User created: {$u['username']} ({$u['role']})\n";
    }

    echo "\n===========================================\n";
    echo "Seeding Medicine & Vaccine Inventory...\n";
    echo "===========================================\n";

    $medicines = [
        ['MED-0001', 'Paracetamol 500mg (Biogesic)', 'Tablet for fever and pain relief.', 'Medicine', 1500, 150],
        ['MED-0002', 'Amoxicillin 500mg', 'Broad-spectrum antibiotic capsule.', 'Medicine', 80, 100], // Low stock alert trigger
        ['MED-0003', 'BCG Vaccine', 'Tuberculosis vaccine for infants.', 'Vaccine', 50, 10],
        ['MED-0004', 'Hepatitis B Vaccine', 'Hepatitis B preventative vaccine.', 'Vaccine', 8, 15], // Low stock alert trigger
        ['MED-0005', 'Sterile Syringe 3ml', 'Disposable syringe for clinical injections.', 'Supply', 300, 30],
        ['MED-0006', 'Adhesive Bandages (Band-Aid)', 'Supplies for minor wound dressing.', 'Supply', 500, 50],
        ['MED-0007', 'Oral Contraceptive Pills (Micropil)', 'Contraceptive pills for family planning.', 'Family Planning', 120, 20],
        ['MED-0008', 'Condoms (Trust)', 'Barrier method contraception.', 'Family Planning', 300, 50]
    ];

    $stmtMed = $db->prepare("
        INSERT INTO medicines (code, name, description, category, stock_qty, reorder_level)
        VALUES (:code, :name, :description, :category, :stock_qty, :reorder_level)
    ");

    foreach ($medicines as $m) {
        $stmtMed->execute([
            ':code' => $m[0],
            ':name' => $m[1],
            ':description' => $m[2],
            ':category' => $m[3],
            ':stock_qty' => $m[4],
            ':reorder_level' => $m[5]
        ]);
        echo " -> Medicine registered: {$m[1]} ({$m[3]})\n";
    }

    echo "\n===========================================\n";
    echo "Seeding Residents...\n";
    echo "===========================================\n";

    $residents = [
        ['RES-2026-0001', 'Juan', 'Perez', 'Dela Cruz', 'Male', '1985-04-12', 'Married', '09171234567', 'Sitio 2, Barangay Health Center', 1, null, null],
        ['RES-2026-0002', 'Maria', 'Santos', 'Dela Cruz', 'Female', '1988-11-20', 'Married', '09187654321', 'Sitio 2, Barangay Health Center', 0, 'Not Pregnant', null],
        ['RES-2026-0003', 'Pedrito', 'Santos', 'Dela Cruz', 'Male', '2023-05-15', 'Single', '', 'Sitio 2, Barangay Health Center', 0, null, 'Breastfeeding'],
        ['RES-2026-0004', 'Elizabeth', 'Gomez', 'Alvarez', 'Female', '1955-08-30', 'Widowed', '09228889999', 'Blk 5 Lot 2, Barangay Health Center', 1, 'Not Pregnant', null],
        ['RES-2026-0005', 'Carlo', 'Rodriguez', 'Aquino', 'Male', '1995-02-28', 'Single', '09051112222', 'Sitio 4, Barangay Health Center', 1, null, null]
    ];

    $stmtRes = $db->prepare("
        INSERT INTO residents (resident_id, first_name, middle_name, last_name, gender, birthdate, civil_status, contact_number, address, is_family_head, pregnancy_status, child_feeding_type, status)
        VALUES (:resident_id, :first_name, :middle_name, :last_name, :gender, :birthdate, :civil_status, :contact_number, :address, :is_family_head, :pregnancy_status, :child_feeding_type, 'Active')
    ");

    foreach ($residents as $r) {
        $stmtRes->execute([
            ':resident_id' => $r[0],
            ':first_name' => $r[1],
            ':middle_name' => $r[2],
            ':last_name' => $r[3],
            ':gender' => $r[4],
            ':birthdate' => $r[5],
            ':civil_status' => $r[6],
            ':contact_number' => $r[7],
            ':address' => $r[8],
            ':is_family_head' => $r[9],
            ':pregnancy_status' => $r[10],
            ':child_feeding_type' => $r[11]
        ]);
        echo " -> Resident registered: {$r[3]}, {$r[1]}\n";
    }

    echo "\n===========================================\n";
    echo "Seeding Family Profiles...\n";
    echo "===========================================\n";

    // Get Resident IDs
    $stmtId = $db->query("SELECT id, resident_id FROM residents");
    $resMap = [];
    while ($row = $stmtId->fetch()) {
        $resMap[$row['resident_id']] = $row['id'];
    }

    // 1. Create Family FAM-2026-0001 with head Juan Dela Cruz
    $db->prepare("
        INSERT INTO families (
            family_no, head_resident_id, address,
            occupation, educational_attainment,
            family_planning_status, toilet_type,
            water_source, food_production_activity
        )
        VALUES (
            'FAM-2026-0001', :head, 'Sitio 2, Barangay Health Center',
            'Farmer', 'High School Graduate',
            'Condom', 'Water-sealed (Flush)',
            'Piped Water', 'Backyard Gardening'
        )
    ")->execute([':head' => $resMap['RES-2026-0001']]);
    
    $familyId = $db->lastInsertId();

    // 2. Link Family Members (Juan-Head, Maria-Spouse, Pedrito-Child)
    $stmtFM = $db->prepare("
        INSERT INTO family_members (family_id, resident_id, relationship_to_head)
        VALUES (:family_id, :resident_id, :relationship)
    ");

    $stmtFM->execute([':family_id' => $familyId, ':resident_id' => $resMap['RES-2026-0001'], ':relationship' => 'Head']);
    $stmtFM->execute([':family_id' => $familyId, ':resident_id' => $resMap['RES-2026-0002'], ':relationship' => 'Spouse']);
    $stmtFM->execute([':family_id' => $familyId, ':resident_id' => $resMap['RES-2026-0003'], ':relationship' => 'Child']);
    echo " -> Family profile FAM-2026-0001 created (3 members linked).\n";

    echo "\n===========================================\n";
    echo "Seeding Consultations...\n";
    echo "===========================================\n";

    // Get User IDs (Worker ID)
    $workerId = $db->query("SELECT id FROM users WHERE username = 'worker'")->fetchColumn();
    $medMap = [];
    $stmtMedId = $db->query("SELECT id, code FROM medicines");
    while ($row = $stmtMedId->fetch()) {
        $medMap[$row['code']] = $row['id'];
    }

    $consultations = [
        [
            'consultation_no' => 'CON-2026-0001',
            'resident_id' => $resMap['RES-2026-0002'], // Maria Dela Cruz
            'user_id' => $workerId,
            'symptoms' => 'Patient complained of runny nose, sore throat, and mild fever (38.2°C) since yesterday.',
            'diagnosis' => 'Acute Nasopharyngitis (Common Cold)',
            'treatment' => 'Drink plenty of warm water. Take paracetamol every 4 hours if fever persists.',
            'medicine_id' => $medMap['MED-0001'], // Paracetamol
            'medicine_qty' => 15,
            'consultation_date' => date('Y-m-d', strtotime('-2 days')),
            'status' => 'Completed'
        ],
        [
            'consultation_no' => 'CON-2026-0002',
            'resident_id' => $resMap['RES-2026-0004'], // Elizabeth Alvarez (Senior)
            'user_id' => $workerId,
            'symptoms' => 'Routine checkup. Blood pressure reading was 140/90 mmHg.',
            'diagnosis' => 'Mild Hypertension',
            'treatment' => 'Advised low-salt diet. Rest. Avoid stressful situations. Schedule next visit in 2 weeks.',
            'medicine_id' => null,
            'medicine_qty' => null,
            'consultation_date' => date('Y-m-d', strtotime('-1 days')),
            'status' => 'Completed'
        ]
    ];

    $stmtCon = $db->prepare("
        INSERT INTO consultations (consultation_no, resident_id, user_id, symptoms, diagnosis, treatment, medicine_id, medicine_qty, consultation_date, status)
        VALUES (:consultation_no, :resident_id, :user_id, :symptoms, :diagnosis, :treatment, :medicine_id, :medicine_qty, :consultation_date, :status)
    ");

    $stmtDist = $db->prepare("
        INSERT INTO medicine_distributions (medicine_id, resident_id, quantity, distribution_date, user_id)
        VALUES (:medicine_id, :resident_id, :quantity, :distribution_date, :user_id)
    ");

    foreach ($consultations as $c) {
        $stmtCon->execute([
            ':consultation_no' => $c['consultation_no'],
            ':resident_id' => $c['resident_id'],
            ':user_id' => $c['user_id'],
            ':symptoms' => $c['symptoms'],
            ':diagnosis' => $c['diagnosis'],
            ':treatment' => $c['treatment'],
            ':medicine_id' => $c['medicine_id'],
            ':medicine_qty' => $c['medicine_qty'],
            ':consultation_date' => $c['consultation_date'],
            ':status' => $c['status']
        ]);
        
        // Log distribution if medicine was given
        if ($c['medicine_id'] !== null) {
            $stmtDist->execute([
                ':medicine_id' => $c['medicine_id'],
                ':resident_id' => $c['resident_id'],
                ':quantity' => $c['medicine_qty'],
                ':distribution_date' => $c['consultation_date'],
                ':user_id' => $c['user_id']
            ]);
            
            // Deduct stock from database
            $db->prepare("UPDATE medicines SET stock_qty = stock_qty - :qty WHERE id = :id")
               ->execute([':qty' => $c['medicine_qty'], ':id' => $c['medicine_id']]);
        }
        echo " -> Consultation registered: {$c['consultation_no']}\n";
    }

    echo "\n===========================================\n";
    echo "Seeding Immunization Records...\n";
    echo "===========================================\n";

    $immunizations = [
        [
            'resident_id' => $resMap['RES-2026-0003'], // Pedrito Dela Cruz (Infant/Child)
            'vaccine_id' => $medMap['MED-0003'], // BCG Vaccine
            'dose' => '1st Dose',
            'date_given' => '2023-06-01',
            'next_schedule' => '2023-09-01',
            'user_id' => $workerId,
            'status' => 'Completed'
        ],
        [
            'resident_id' => $resMap['RES-2026-0003'], // Pedrito Dela Cruz
            'vaccine_id' => $medMap['MED-0004'], // HepB Vaccine
            'dose' => '1st Dose',
            'date_given' => null,
            'next_schedule' => date('Y-m-d', strtotime('+7 days')), // Future schedule
            'user_id' => $workerId,
            'status' => 'Upcoming'
        ]
    ];

    $stmtImm = $db->prepare("
        INSERT INTO immunizations (resident_id, vaccine_id, dose, date_given, next_schedule, user_id, status)
        VALUES (:resident_id, :vaccine_id, :dose, :date_given, :next_schedule, :user_id, :status)
    ");

    foreach ($immunizations as $i) {
        $stmtImm->execute([
            ':resident_id' => $i['resident_id'],
            ':vaccine_id' => $i['vaccine_id'],
            ':dose' => $i['dose'],
            ':date_given' => $i['date_given'],
            ':next_schedule' => $i['next_schedule'],
            ':user_id' => $i['user_id'],
            ':status' => $i['status']
        ]);
        
        // Log distribution if completed
        if ($i['status'] === 'Completed') {
            $stmtDist->execute([
                ':medicine_id' => $i['vaccine_id'],
                ':resident_id' => $i['resident_id'],
                ':quantity' => 1,
                ':distribution_date' => $i['date_given'],
                ':user_id' => $i['user_id']
            ]);
            
            // Deduct stock
            $db->prepare("UPDATE medicines SET stock_qty = stock_qty - 1 WHERE id = :id")
               ->execute([':id' => $i['vaccine_id']]);
        }
        echo " -> Immunization logged: dose {$i['dose']} for vaccine ID: {$i['vaccine_id']}\n";
    }

    echo "\n===========================================\n";
    echo "Seeding Audit Activity Logs...\n";
    echo "===========================================\n";

    $logs = [
        ['LOGIN', 'Logged into the system successfully.', $workerId, '127.0.0.1'],
        ['CREATE_RESIDENT', 'Registered resident: Dela Cruz, Juan', $workerId, '127.0.0.1'],
        ['CREATE_RESIDENT', 'Registered resident: Dela Cruz, Maria', $workerId, '127.0.0.1'],
        ['CREATE_RESIDENT', 'Registered resident: Dela Cruz, Pedrito', $workerId, '127.0.0.1'],
        ['CREATE_FAMILY', 'Created family profile FAM-2026-0001', $workerId, '127.0.0.1'],
        ['CREATE_CONSULTATION', 'Logged consultation CON-2026-0001', $workerId, '127.0.0.1']
    ];

    $stmtLog = $db->prepare("
        INSERT INTO activity_logs (action, description, user_id, ip_address, user_agent)
        VALUES (:action, :description, :user_id, :ip_address, 'CLI Setup Seeder')
    ");

    foreach ($logs as $l) {
        $stmtLog->execute([
            ':action' => $l[0],
            ':description' => $l[1],
            ':user_id' => $l[2],
            ':ip_address' => $l[3]
        ]);
    }
    echo " -> Seseeded activity logs audit trail.\n";

    echo "\n===========================================\n";
    echo "DATABASE SETUP COMPLETED SUCCESSFULLY!\n";
    echo "===========================================\n";

} catch (Exception $e) {
    echo "\nFATAL DATABASE ERROR:\n" . $e->getMessage() . "\n";
    exit(1);
}
