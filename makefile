

.PHONY: help install
.DEFAULT_GOAL := help


help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'


# --------------------------------------------------------------------------

symlink: ## delete the global folder and symlink
	sudo rm -rf ~/.composer/vendor/marcofaul/valet-plus-reforged/*
	ln -s ${PWD}/* ${HOME}/.composer/vendor/marcofaul/valet-plus-reforged/


