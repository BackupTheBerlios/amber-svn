PACKAGE = Amber
PHPDOC = $(shell which phpdoc 2> /dev/null)
DATE = $(shell date +%Y%m%d)
BASEDIR = $(shell basename `pwd`)

NORMAL = \e[0m
GREEN = \e[32;01m
YELLOW = \e[33;01m

all:
	@echo
	@echo
	@echo -e "$(YELLOW)NOTE: This Makefile is intented to be used by developers only!!!$(NORMAL)"
	@echo
	@echo
	@echo -e "Possible targets:"
	@echo
	@echo -e "$(GREEN)all$(NORMAL)        Show this information"
	@echo -e "$(GREEN)docu$(NORMAL)       Generates class documentation (requires phpdoc)"
	@echo -e "$(GREEN)docuclean$(NORMAL)  Removes class documentation"
	@echo -e "$(GREEN)clean$(NORMAL)      Cleans cache"
	@echo -e "$(GREEN)distclean$(NORMAL)  Removes class documentation and cleans cache"
	@echo -e "$(GREEN)tgz$(NORMAL)        Cleans cache and creates a gzip'ed archive in the parents folder"
	@echo

docu: docuclean
	@if [ -z "$(PHPDOC)" ] ; then \
		echo -e "\n$(YELLOW)ERROR: phpDocumentor not found in path!$(NORMAL)\n"; \
	else \
		$(PHPDOC) -q -t doc -d $(PACKAGE) -ti $(PACKAGE) -pp on -o HTML:frames:DOM/phphtmllib; \
	fi
tgz: clean
	cd .. ; tar -czf ./$(DATE)-$(PACKAGE).tgz $(BASEDIR)

distclean: clean docuclean

docuclean:
	rm -rf doc/*

clean:
	rm -rf examples/cache/*
