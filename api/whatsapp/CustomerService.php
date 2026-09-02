<?php
class CustomerService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function cleanRut($rut) {
        $rut = preg_replace('/[^0-9kK]/', '', $rut);
        return strtoupper($rut);
    }

    public function validateRut($rut) {
        $rut = $this->cleanRut($rut);
        if (strlen($rut) < 8) return false;
        
        $cuerpo = substr($rut, 0, -1);
        $dv = substr($rut, -1);
        
        $suma = 0;
        $multiplo = 2;
        
        for ($i = 1; $i <= strlen($cuerpo); $i++) {
            $index = $multiplo * $cuerpo[strlen($cuerpo) - $i];
            $suma = $suma + $index;
            if ($multiplo < 7) { $multiplo = $multiplo + 1; } else { $multiplo = 2; }
        }
        
        $dvEsperado = 11 - ($suma % 11);
        $dvEsperado = ($dvEsperado == 11) ? 0 : (($dvEsperado == 10) ? "K" : $dvEsperado);
        
        return $dv == $dvEsperado;
    }

    public function findByPhoneOrRut($phone, $rut = null) {
        $query = "SELECT * FROM leads WHERE phone = :phone";
        $params = [':phone' => $phone];
        
        if ($rut) {
            $rut = $this->cleanRut($rut);
            $query .= " OR rut = :rut";
            $params[':rut'] = $rut;
        }
        
        $stmt = $this->pdo->prepare($query . " ORDER BY id DESC LIMIT 1");
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createOrUpdateLead($data) {
        $existing = $this->findByPhoneOrRut($data['phone'], $data['rut'] ?? null);
        
        if ($existing) {
            // Update
            $sql = "UPDATE leads SET ";
            $updates = [];
            $params = [];
            foreach (['name', 'rut', 'company', 'role', 'region', 'comuna', 'comments'] as $field) {
                if (isset($data[$field]) && !empty($data[$field])) {
                    $updates[] = "`$field` = :$field";
                    $params[":$field"] = $data[$field];
                }
            }
            if (empty($updates)) return $existing['id'];
            
            $sql .= implode(", ", $updates) . " WHERE id = :id";
            $params[':id'] = $existing['id'];
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $existing['id'];
        } else {
            // Insert
            $sql = "INSERT INTO leads (name, rut, company, role, phone, region, comuna, comments, origin, status) 
                    VALUES (:name, :rut, :company, :role, :phone, :region, :comuna, :comments, 'WhatsApp Bot', 'Nuevo')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':name' => $data['name'] ?? '',
                ':rut' => $data['rut'] ?? null,
                ':company' => $data['company'] ?? null,
                ':role' => $data['role'] ?? null,
                ':phone' => $data['phone'] ?? null,
                ':region' => $data['region'] ?? null,
                ':comuna' => $data['comuna'] ?? null,
                ':comments' => $data['comments'] ?? null
            ]);
            return $this->pdo->lastInsertId();
        }
    }
}
