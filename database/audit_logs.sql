CREATE TABLE IF NOT EXISTS audit_logs (
  id bigserial PRIMARY KEY,
  owner_id bigint NOT NULL,
  actor_id bigint,
  actor_type text NOT NULL,
  actor_roles jsonb DEFAULT '[]'::jsonb,
  gestor text NOT NULL,
  action text NOT NULL,
  status text NOT NULL, -- 'success'|'error'|'denied'
  target_table text,
  target_id text,
  old jsonb,
  new jsonb,
  meta jsonb DEFAULT '{}'::jsonb,
  created_at timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_audit_owner_created ON audit_logs (owner_id, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_audit_actor ON audit_logs (actor_id);
CREATE INDEX IF NOT EXISTS idx_audit_gestor_action ON audit_logs (gestor, action);