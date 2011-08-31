#!/bin/bash

version="0.60.1"

if [ ! -d build ] ; then
	mkdir build
fi

if [ ! -d dist ] ; then
	mkdir dist
fi

target="com.inamik.simpledl-$version"

build_dir="build/$target"
dist_dir="dist/$target"

if [ -d "$build_dir" ] ; then
	rm -rf "$build_dir"
fi

if [ -d "$dist_dir" ] ; then
	rm -rf "$dist_dir"
fi

mkdir $build_dir

cp -r dl          $build_dir/
cp -r log         $build_dir/
cp    gpl.txt     $build_dir/
cp    index.php   $build_dir/
cp    README.md   $build_dir/
cp    VERSION.txt $build_dir/

mkdir $dist_dir

tar -zcf $dist_dir/$target.tar.gz -C build $target/
