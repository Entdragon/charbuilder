#!/bin/bash

PLUGIN_DIR="$HOME/public_html/wp-content/plugins/character-generator-dev"
NAMESPACE_NAME="CharacterGeneratorDev"

# Loop through all PHP files in the plugin
find "$PLUGIN_DIR" -type f -name "*.php" | while read -r file; do
  # Skip files that already have a namespace
  if grep -qP '^\s*namespace\s+[a-zA-Z0-9_\\]+' "$file"; then
    echo "[SKIP] Already namespaced: $file"
    continue
  fi

  # Make sure it’s a PHP file with code (not empty or comment-only)
  if ! grep -qP '\bfunction\b|\bclass\b|\bconst\b|\$' "$file"; then
    echo "[SKIP] Looks empty or minimal: $file"
    continue
  fi

  echo "[WRAP] Wrapping file: $file"

  # Wrap entire contents in namespace block
  {
    echo "<?php"
    echo
    echo "namespace $NAMESPACE_NAME {"
    echo
    sed '1{/^<\?php/d}' "$file"  # Remove existing <?php from top
    echo
    echo "} // namespace $NAMESPACE_NAME"
  } > "$file.tmp" && mv "$file.tmp" "$file"
done

echo "✅ All files processed and namespaced."
