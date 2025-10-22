<?php
<<<<<<< HEAD
// config/constants.php

// NOTE: Do not send a global Content-Type header here because this file
// is included by both API endpoints (JSON) and pages that must return HTML.
// Individual scripts should set the appropriate Content-Type when needed.
=======
header('Content-Type: application/json; charset=utf-8');
>>>>>>> camilo-dev
define('SUPABASE_URL', 'https://gqcaeecfhqdkpoatmkfs.supabase.co');
define('SUPABASE_SERVICE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImdxY2FlZWNmaHFka3BvYXRta2ZzIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc2MDMxMTQ2NCwiZXhwIjoyMDc1ODg3NDY0fQ.oZwMCLAPcu75_8VINHSzPXzyJA3ZZ01l8ldhzF2h0i0'); // Usa Service Role Key (no anon)
define('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImdxY2FlZWNmaHFka3BvYXRta2ZzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjAzMTE0NjQsImV4cCI6MjA3NTg4NzQ2NH0.agLbF4-RvJYdSbnMavQxkG-1sicOs0Yn-Mlsk688Ayc');
?>