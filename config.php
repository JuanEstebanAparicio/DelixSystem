<?php
// Tu URL y Service Key (de Supabase > Project Settings > API)
define("SUPABASE_URL", "https://gqcaeecfhqdkpoatmkfs.supabase.co");
define("SUPABASE_KEY", "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImdxY2FlZWNmaHFka3BvYXRta2ZzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjAzMTE0NjQsImV4cCI6MjA3NTg4NzQ2NH0.agLbF4-RvJYdSbnMavQxkG-1sicOs0Yn-Mlsk688Ayc");

// Encabezados comunes
function supabaseHeaders() {
    return [
        "Content-Type: application/json",
        "apikey: " . SUPABASE_KEY,
        "Authorization: Bearer " . SUPABASE_KEY
    ];
}
?>
