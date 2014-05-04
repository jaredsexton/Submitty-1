/* Sample homework 1 */

#include <iostream>
#include <fstream>
#include <cstdlib>

int main( int argc, char* argv[] ) {

	if( argc != 3 ) {	
		std::cerr << "Usage: " << argv[0] << " <an integer> <output file>" << std::endl;
		return 1;
	}
	
	int num = atoi(argv[1]);
	
	int squared = num*num;
	
	std::ofstream outstr(argv[2]);
	if( !outstr ) {
		std::cerr << "File " << argv[2] << " not found" << std::endl;
		return 1;
	}
	
	outstr << "Squaring " << num << " gives you " << squared << std::endl;
	
	return 0;
}
