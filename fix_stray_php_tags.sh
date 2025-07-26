#!/bin/bash
PLUGIN_DIR="$HOME/public_html/wp-content/plugins/character-generator-dev"
BACKUP_DIR="${PLUGIN_DIR}/backup_php_fix_$(date +%s)"
mkdir -p "$BACKUP_DIR"

echo "🔍 Scanning for stray <?php tags in namespaced files..."

find "$PLUGIN_DIR" -type f -name '*.php' | while read -r file; do
  # Only target files with the target namespace and multiple <?php tags
  if grep -q 'namespace CharacterGeneratorDev' "$file" && grep -q "<?php" "$file"; then
    # Count how many <?php tags exist
    tag_count=$(grep -c "<?php" "$file")
    if [ "$tag_count" -gt 1 ]; then
      echo "⚠️  Stray <?php found in: $file"
      cp "$file" "$BACKUP_DIR"

      # Use awk to keep only the first <?php and remove others
      awk '
      BEGIN { php_count=0 }
      {
        if ($0 ~ /<\?php/) {
          php_count++
          if (php_count == 1) {
            print
          }
        } else {
          print
        }
      }' "$file" > "${file}.fixed" && mv "${file}.fixed" "$file"
    fi
  fi
done

echo "✅ Cleanup complete. Backup stored at: $BACKUP_DIR"
