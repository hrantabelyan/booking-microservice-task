#!/bin/bash
# One-time host setup. Run from the repo root after cloning.
set -e

echo "Pointing git at .githooks/ for pre-push checks..."
git config core.hooksPath .githooks

echo "Done. The pre-push hook will run on your next push (requires the php container to be up)."
