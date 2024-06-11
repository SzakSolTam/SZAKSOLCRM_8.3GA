module.exports = function(grunt) {

    // Project configuration
    grunt.initConfig({
        less: {
            development: {
                options: {
                    paths: ['less']
                },
                files: {
                    '../marketing/style.css': '../marketing/style.less',
                    '../sales/style.css': '../sales/style.less',
                    '../contact/style.css': '../contact/style.less',
                    '../inventory/style.css': '../inventory/style.less',
                    '../marketing_and_sales/style.css': '../marketing_and_sales/style.less',
                    '../project/style.css': '../project/style.less',
                    '../support/style.css': '../support/style.less',
                    '../tools/style.css': '../tools/style.less',
                }
            }
        },
        watch: {
            styles: {
                files: ['../**/*.less'], // Watch for changes in all Less files within the 'less' directory
                tasks: ['less'], // Run the 'less' task when a change is detected
                options: {
                    livereload: true // Enable live reloading in the browser (if you have a browser extension installed)
                }
            }
        }
    });

    // Load the plugins
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');

    // Default task(s)
    grunt.registerTask('default', ['less']);
    grunt.registerTask('dev', ['less', 'watch']);

};
