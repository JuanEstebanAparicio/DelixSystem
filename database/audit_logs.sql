CREATE TABLE IF NOT EXISTS audit_logs (
  id bigserial PRIMARY KEY,
  owner_id bigint NOT NULL,               -- propietario dueño del historial
  actor_id bigint,                        -- quien hizo la acción
  actor_type text NOT NULL,               -- 'owner' | 'employee'
  actor_roles jsonb DEFAULT '[]'::jsonb,  -- roles del actor al momento
  gestor text NOT NULL,                   -- gestor donde ocurrió la acción
  action text NOT NULL,                   -- acción realizada
  target_table text,                      
  target_id text,
  old jsonb,
  new jsonb,
  meta jsonb DEFAULT '{}'::jsonb,
  created_at timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_audit_owner_created 
  ON audit_logs (owner_id, created_at DESC);

