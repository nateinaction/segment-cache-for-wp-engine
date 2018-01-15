#!/usr/bin/env bash

build_name="segment-cache-wpe"
version=$(grep 'Version:' ${build_name}.php | cut -d' ' -f4)
zip_name="${build_name}-${version}.zip"

# If build zip already exists, remove it
if [ -f ${zip_name} ]; then
  echo "Removing previous ${zip_name}"
  rm ${zip_name}
fi

# If build dir already exists, remove it
if [ -d ${build_name} ]; then
  echo "Removing previous build directory"
  rm -rf ${build_name}
fi

# run build
echo "Building..."
mkdir ${build_name}
rsync ${build_name}.php ${build_name}/

# copy src dir
mkdir ${build_name}/src
rsync -r src/ ${build_name}/src/

# copy vendor dir
mkdir ${build_name}/vendor
rsync -r vendor/ ${build_name}/vendor/

# remove .DS_Store from build directory
find ${build_name}/ -name ".DS_Store" -depth -exec rm {} \

# package zip
zip -r "${zip_name}" ${build_name}/

# remove build directory
rm -rf ${build_name}

echo "Build available at ${zip_name}"