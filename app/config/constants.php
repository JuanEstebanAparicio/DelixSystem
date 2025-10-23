<?php
/**
 *  Define las constantes globales necesarias para la conexión con el proyecto 
 *  de Supabase. Estas constantes son utilizadas por el módulo `supabase.php`
 *  para autenticar las peticiones HTTP hacia la base de datos y los servicios
 *  de autenticación de Supabase.
 *  - Nunca expongas la SERVICE_KEY en el frontend o repositorios públicos.
 *  - Este archivo debe ser incluido en el backend (PHP) únicamente.
 */

define('SUPABASE_URL', 'https://gqcaeecfhqdkpoatmkfs.supabase.co');
define('SUPABASE_SERVICE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImdxY2FlZWNmaHFka3BvYXRta2ZzIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc2MDMxMTQ2NCwiZXhwIjoyMDc1ODg3NDY0fQ.oZwMCLAPcu75_8VINHSzPXzyJA3ZZ01l8ldhzF2h0i0'); // Usa Service Role Key (no anon)
define('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImdxY2FlZWNmaHFka3BvYXRta2ZzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjAzMTE0NjQsImV4cCI6MjA3NTg4NzQ2NH0.agLbF4-RvJYdSbnMavQxkG-1sicOs0Yn-Mlsk688Ayc');
?>