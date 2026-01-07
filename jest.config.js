/**
 * Jest Configuration
 *
 * @package Consent_Raven
 */

module.exports = {
	testEnvironment: 'jsdom',
	roots: ['<rootDir>/tests/jest'],
	testMatch: ['**/*.test.js'],
	moduleFileExtensions: ['js', 'jsx', 'json'],
	transform: {
		'^.+\\.jsx?$': 'babel-jest',
	},
	setupFilesAfterEnv: ['<rootDir>/tests/jest/setup.js'],
	moduleNameMapper: {
		'\\.(css|less|scss|sass)$': 'identity-obj-proxy',
		'^@wordpress/(.*)$': '<rootDir>/node_modules/@wordpress/$1',
	},
	collectCoverageFrom: [
		'assets/js/**/*.js',
		'admin/src/**/*.js',
		'!**/node_modules/**',
	],
	coverageDirectory: 'coverage',
	coverageReporters: ['text', 'lcov', 'html'],
	verbose: true,
	testTimeout: 10000,
	globals: {
		'ts-jest': {
			diagnostics: false,
		},
	},
};
