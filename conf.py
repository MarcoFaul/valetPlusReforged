# Configuration file for the Sphinx documentation builder.
#
# This file only contains a selection of the most common options. For a full
# list see the documentation:
# https://www.sphinx-doc.org/en/master/usage/configuration.html

# -- Path setup --------------------------------------------------------------

# If extensions (or modules to document with autodoc) are in another directory,
# add these directories to sys.path here. If the directory is relative to the
# documentation root, use os.path.abspath to make it absolute, like shown here.
#
import sys, os
import sphinx_rtd_theme

import recommonmark
from recommonmark.transform import AutoStructify

from sphinx.highlighting import lexers
from pygments.lexers.web import PhpLexer
from pygments.lexers.web import HtmlLexer
from pygments.lexers.web import JsonLexer

from datetime import datetime

lexers['php'] = PhpLexer(startinline=True)
lexers['php-annotations'] = PhpLexer(startinline=True)
lexers['html'] = HtmlLexer(startinline=True)
lexers['json'] = JsonLexer(startinline=True)



# -- Project information -----------------------------------------------------

project = 'Valet plus reforged'
copyright = '2020, Marco Faul'
author = 'Marco Faul'
version = ''
release = ''

# -- General configuration ---------------------------------------------------

# Add any Sphinx extension module names here, as strings. They can be
# extensions coming with Sphinx (named 'sphinx.ext.*') or your custom
# ones.
extensions = [
  'recommonmark',
  'sphinx_rtd_theme',
  'sphinx_copybutton',
  'sphinx_markdown_tables',
]
# Add any paths that contain templates here, relative to this directory.
source_suffix = ['.rst', '.md']
master_doc = 'index'

# List of patterns, relative to source directory, that match files and
# directories to ignore when looking for source files.
# This pattern also affects html_static_path and html_extra_path.
exclude_patterns = ['_build', 'Thumbs.db', '.DS_Store']


# -- Options for HTML output -------------------------------------------------

exclude_patterns = ['_build']

html_theme = "sphinx_rtd_theme"
html_theme_path = [sphinx_rtd_theme.get_html_theme_path()]
html_show_sourcelink = False

html_extra_path = ['_redirects']

def setup(app):
    app.add_config_value('recommonmark_config', {
        'auto_toc_tree_section': ['Table of Contents'],
        'enable_math': False,
        'enable_inline_math': False,
        'enable_eval_rst': True,
    }, True)
    app.add_transform(AutoStructify)
