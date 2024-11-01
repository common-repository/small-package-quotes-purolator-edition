<?php

/**
 * transit days
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists("EnPurolatorSmallTransitDays")) {

    class EnPurolatorSmallTransitDays
    {

        public function __construct()
        {

        }

        public function estimated_arrival_days($EstimatedArrival)
        {
            $Arrival = (isset($EstimatedArrival->Arrival->Date)) ? $EstimatedArrival->Arrival->Date : "";
            $Pickup = (isset($EstimatedArrival->Pickup->Date)) ? $EstimatedArrival->Pickup->Date : "";
            return (int)human_time_diff(strtotime($Pickup), strtotime($Arrival));
        }

        public function purolator_enable_disable_ground_service($result)
        {
            $transit_day_type = get_option('restrict_calendar_transit_small_packages_purolator'); //get value of check box to see which one is checked
            $days_to_restrict = get_option('restrict_days_transit_package_purolator_small');
            $action = get_option("purolator_small_package");
            $package = $transit_days = apply_filters('purolator_small_quotes_plans_suscription_and_features', 'transit_days');
            $package = (isset($package) && ($package == 1 || $package == 2)) ? TRUE : FALSE;
            $ServiceSummary = isset($result->q) && !empty($result->q) ? $result->q : [];
            $ServiceSummary = isset($ServiceSummary) && !empty($ServiceSummary) ? $ServiceSummary : [];
            if ($package && strlen($days_to_restrict) > 0 && strlen($transit_day_type) > 0) {
                foreach ($ServiceSummary as $key => $service) {
                    if (isset($service->serviceType) && (($service->serviceType == "PurolatorGround") || ($service->serviceType == "PurolatorGroundU.S."))) {
                        $estimated_arrival_days = 0;
                        if ($transit_day_type == 'BusinessDaysInTransit') {
                            $estimated_arrival_days = (isset($service->transitTime)) ? $service->transitTime : 0;
                        } elseif ($transit_day_type == 'CalenderDaysInTransit') {
                            $estimated_arrival_days = (isset($service->TransitTimeInDays)) ? $service->TransitTimeInDays : 0;
                        }

                        if ($estimated_arrival_days > 0 && $estimated_arrival_days > $days_to_restrict) {
                            unset($result->q[$key]);
                        }
                    }
                }
            }
            return $result;
        }

    }
}
        

