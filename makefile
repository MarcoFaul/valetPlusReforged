

.PHONY: help install
.DEFAULT_GOAL := help


help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'


# --------------------------------------------------------------------------

override: ## Execute locale valet file
	sudo rm -rf ~/.composer/vendor/marcofaul/valet-plus-reforged/* && sudo cp -R ./ ~/.composer/vendor/marcofaul/valet-plus-reforged
