-- Manual rollback only after reviewing/exporting block data.
ALTER TABLE `t_pawn_sums`
  DROP COLUMN `block_reason`,
  DROP COLUMN `blocked_by`,
  DROP COLUMN `blocked_at`,
  DROP COLUMN `is_blocked`;

ALTER TABLE `t_opening_pawn_sums`
  DROP COLUMN `block_reason`,
  DROP COLUMN `blocked_by`,
  DROP COLUMN `blocked_at`,
  DROP COLUMN `is_blocked`;
