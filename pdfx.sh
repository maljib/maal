#!/bin/bash
if [ "$(uname)" == "Darwin" ]; then
    export TEXMFVAR="/Applications/XAMPP/xamppfiles/htdocs/.texlive"
    /Library/TeX/texbin/lualatex --interaction=nonstopmode --output-directory=$2 $1
else
    export TEXMFVAR="/var/www/.texlive"
    /usr/bin/lualatex --interaction=nonstopmode --output-directory=$2 $1
fi

#gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=${1}_.pdf $1.pdf
#mv ${1}_.pdf $1.pdf
