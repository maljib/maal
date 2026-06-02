#!/bin/bash
if [ "$(uname)" == "Darwin" ]; then
    /Library/TeX/texbin/lualatex --interaction=nonstopmode --output-directory=$2 $1
else
    /usr/bin/lualatex --interaction=nonstopmode --output-directory=$2 $1
fi

#gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=${1}_.pdf $1.pdf
#mv ${1}_.pdf $1.pdf
