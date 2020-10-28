<?php

namespace AppBundle\Repository;
use AppBundle\Entity\Job;
use AppBundle\Entity\ProfileDetails;

/**
 * JobRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class JobRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param $id
     * @param array $params
     * @return mixed
     */
    public function getClientDashboard($id, $params=array()){
        $query = $this->createQueryBuilder('j')
            ->select('j.id, j.jobTitle, j.closureDate,'.
                '(Select COUNT(a.id) FROM AppBundle:Applicants as a WHERE a.job = j.id AND a.status = 1) as awaitingCount,'.
                '(Select COUNT(a1.id) FROM AppBundle:Applicants as a1 WHERE a1.job = j.id AND a1.status = 2) as shortListCount,'.
                '(Select COUNT(a2.id) FROM AppBundle:Applicants as a2 WHERE a2.job = j.id AND a2.status = 3) as approvedCount'
            )
            ->where("j.user = :id")
            ->setParameter('id', $id)
            ->orderBy('j.closureDate', 'DESC');

        if(isset($params['limit']) && $params['limit']>0){
            $query->setMaxResults($params['limit']);
        }
        $result = $query->getQuery()->getResult();

        $queryCount = $this->createQueryBuilder('j')
            ->select('COUNT(j.id) as loadedJob,'.
                '(Select COUNT(a.id) FROM AppBundle:Applicants as a WHERE a.client = :id AND a.status = 1) as awaitingAll,'.
                '(Select COUNT(a1.id) FROM AppBundle:Applicants as a1 WHERE a1.client = :id AND a1.status = 2) as shortListAll,'.
                '(Select COUNT(a2.id) FROM AppBundle:Applicants as a2 WHERE a2.client = :id AND a2.status = 3) as approvedAll'
            )
            ->where("j.user = :id")
            ->setParameter('id', $id);

        $resultCount = $queryCount->getQuery()->getResult();


        return [
            'jobs' => $result,
            'stats' => $resultCount
        ];
    }

    /**
     * @param $id
     * @param array $params
     * @return mixed
     */
    public function getClientJobsWithoutApplicants($id, $params=array()){
        $query = $this->createQueryBuilder('j')
            ->select('j')
            ->where("j.user = :id")
            ->setParameter('id', $id);

        if(isset($params['status']) && ($params['status'] == 'false' || $params['status'] == false)){
            $query->andWhere('j.status = :status')
                ->setParameter("status", false);
        }
        else{
            $query->andWhere('j.status = :status')
                ->setParameter("status", true);
        }

        if(isset($params['approve'])){
            $approve = null;
            if($params['approve'] == 'false'){
                $approve = false;
            }
            elseif ($params['approve'] == 'true'){
                $approve = true;
            }
            if(!is_null($approve)){
                $query->andWhere('j.approve = :approve')
                    ->setParameter('approve', $approve);
            }
            else{
                $query->andWhere('j.approve is null');
            }

        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $id
     * @param array $params
     * @return mixed
     */
    public function getClientJobs($id, $params=array()){
        $query = $this->createQueryBuilder('j')
            ->select('j.id, j.jobTitle, j.companyAddress as jobAddress, j.created as jobCreated, j.closureDate as jobClosure, j.approve, j.status,'.
                '(Select COUNT(a.id) FROM AppBundle:Applicants as a WHERE a.job = j.id AND a.status = 1) as awaitingCount,'.
                '(Select COUNT(a1.id) FROM AppBundle:Applicants as a1 WHERE a1.job = j.id AND a1.status = 2) as shortListCount,'.
                '(Select COUNT(a2.id) FROM AppBundle:Applicants as a2 WHERE a2.job = j.id AND a2.status = 3) as approvedCount'
            )
            ->where("j.user = :id")
            ->setParameter('id', $id);

        if(isset($params['status']) && ($params['status'] == 'false' || $params['status'] == false)){
            $query->andWhere('j.status = :status')
                ->setParameter("status", false);
        }
        else{
            $query->andWhere('j.status = :status')
                ->setParameter("status", true);
        }

        if(isset($params['approve'])){
            $approve = null;
            if($params['approve'] == 'false' || $params['approve'] == false){
                $approve = false;
            }
            elseif ($params['approve'] == 'true' || $params['approve'] == true){
                $approve = true;
            }
            $query->andWhere('j.approve = :approve')
                ->setParameter('approve', $approve);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $id
     * @param bool $status
     * @return mixed
     */
    public function getClientJobsWithCriteria($id, $status = false){
        $query = $this->createQueryBuilder('j')
            ->select('j.id, j.jobTitle, j.gender, j.ethnicity,
             j.availability, j.location, j.salaryRange')
            ->where("j.user = :id")
            ->setParameter('id', $id);
        if($status == true || $status == 'true'){
            $query->andWhere("j.status = :true")
                ->andWhere("j.approve = :true")
                ->setParameter('true', true);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $userID
     * @param $jobID
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getClientJobById($userID, $jobID){
        return $this->createQueryBuilder('j')
            ->select('j.id, j.jobTitle, j.industry, j.companyName, j.companyAddress, j.addressCountry, j.addressState, j.addressZipCode,'.
                'j.addressCity, j.addressStreet, j.addressStreetNumber, j.addressBuildName, j.addressUnit, j.companyDescription,'.
                'j.roleDescription, j.closureDate, j.articlesFirm, j.gender, j.ethnicity, j.qualification, j.nationality, j.availability,'.
                'j.location, j.salaryRange, j.approve, j.status, j.created as createdDate,'.
                '(Select COUNT(a.id) FROM AppBundle:Applicants as a WHERE a.job = j.id AND a.status = 1) as awaitingCount,'.
                '(Select COUNT(a1.id) FROM AppBundle:Applicants as a1 WHERE a1.job = j.id AND a1.status = 2) as shortListCount,'.
                '(Select COUNT(a2.id) FROM AppBundle:Applicants as a2 WHERE a2.job = j.id AND a2.status = 3) as approvedCount'
            )
            ->where("j.user = :userID")
            ->setParameter('userID', $userID)
            ->andWhere('j.id = :jobID')
            ->setParameter('jobID', $jobID)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function getAllJob($params=array()){
        $query = $this->createQueryBuilder('j')
            ->from("AppBundle:User", "u")
            ->select('j.id, j.companyName, j.jobTitle, j.created as jobDate, u.firstName, u.lastName, 
            u.email, u.phone, j.approve, j.status, j.closureDate, u.id as clientID')
            ->where("j.user = u.id");

        if(isset($params['search']) && !empty($params['search'])){
            $query->andWhere("(u.firstName LIKE :search OR u.lastName LIKE :search OR u.email LIKE :search OR u.phone LIKE :search OR j.companyName LIKE :search OR j.jobTitle LIKE :search)")
                ->setParameter('search', "%".$params['search']."%");
        }

        if(isset($params['status']) && !empty($params['status']) && ($params['status'] == 'false' || $params['status'] == false)){
            $status = false;
        }
        else{
            $status = true;
        }
        $query->andWhere('j.status = :status')
            ->setParameter('status', $status);

        if(isset($params['dateStart']) && !empty($params['dateStart'])){
            $startDate = new \DateTime($params['dateStart']);
            $query->andWhere("DATE_FORMAT(j.created, '%Y-%m-%d') >= :dateStart")
                ->setParameter('dateStart', $startDate->format('Y-m-d'));
        }
        if(isset($params['dateEnd']) && !empty($params['dateEnd'])){
            $endDate = new \DateTime($params['dateEnd']);
            $query->andWhere("DATE_FORMAT(j.created, '%Y-%m-%d') <= :dateEnd")
                ->setParameter('dateEnd', $endDate->format('Y-m-d'));
        }

        if(isset($params['orderBy']) && !empty($params['orderBy']) && in_array($params['orderBy'], ['DaysToGo', 'Company', 'JobTitle', 'Contact', 'Email', 'Phone'])){
            if(isset($params['orderSort']) && !empty($params['orderSort']) && in_array($params['orderSort'], ['asc', 'desc'])){
                if($params['orderBy'] == 'DaysToGo'){
                    $query->orderBy('j.closureDate', $params['orderSort']);
                }
                elseif ($params['orderBy'] == 'Company'){
                    $query->orderBy('j.companyName', $params['orderSort']);
                }
                elseif ($params['orderBy'] == 'JobTitle'){
                    $query->orderBy('j.jobTitle', $params['orderSort']);
                }
                elseif ($params['orderBy'] == 'Contact'){
                    $query->orderBy('u.firstName', $params['orderSort']);
                    $query->addOrderBy('u.lastName', $params['orderSort']);
                }
                elseif ($params['orderBy'] == 'Email'){
                    $query->orderBy('u.email', $params['orderSort']);
                }
                elseif ($params['orderBy'] == 'Phone'){
                    $query->orderBy('u.phone', $params['orderSort']);
                }
            }
            else{
                $query->orderBy('j.created', 'DESC');
            }
        }
        else{
            $query->orderBy('j.created', 'DESC');
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function getJobApprove($params=array()){
        $query = $this->createQueryBuilder('j')
            ->from("AppBundle:User", "u")
            ->select('j.id, j.companyName, j.jobTitle, j.created as jobDate, u.firstName, u.lastName, u.email, u.phone, j.closureDate, u.id as clientID')
            ->where("j.user = u.id")
            ->andWhere("j.approve IS NULL");

        if(isset($params['search']) && !empty($params['search'])){
            $query->andWhere("(u.firstName LIKE :search OR u.lastName LIKE :search OR u.email LIKE :search OR u.phone LIKE :search OR j.companyName LIKE :search OR j.jobTitle LIKE :search)")
                ->setParameter('search', "%".$params['search']."%");
        }

        if(isset($params['orderBy']) && !empty($params['orderBy']) && in_array($params['orderBy'], ['DaysToGo', 'Company', 'JobTitle', 'Contact', 'Email', 'Phone'])){
            if(isset($params['orderSort']) && !empty($params['orderSort']) && in_array($params['orderSort'], ['asc', 'desc'])){
                if($params['orderBy'] == 'DaysToGo'){
                    $query->orderBy('j.closureDate', $params['orderSort']);
                }
                elseif ($params['orderBy'] == 'Company'){
                    $query->orderBy('j.companyName', $params['orderSort']);
                }
                elseif ($params['orderBy'] == 'JobTitle'){
                    $query->orderBy('j.jobTitle', $params['orderSort']);
                }
                elseif ($params['orderBy'] == 'Contact'){
                    $query->orderBy('u.firstName', $params['orderSort']);
                    $query->addOrderBy('u.lastName', $params['orderSort']);
                }
                elseif ($params['orderBy'] == 'Email'){
                    $query->orderBy('u.email', $params['orderSort']);
                }
                elseif ($params['orderBy'] == 'Phone'){
                    $query->orderBy('u.phone', $params['orderSort']);
                }
            }
            else{
                $query->orderBy('j.created', 'DESC');
            }
        }
        else{
            $query->orderBy('j.created', 'DESC');
        }

        if(isset($params['lm']) && $params['lm'] > 0){
            $query->setMaxResults($params['lm'])
                ->setFirstResult(0);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $jobID
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getJobById($jobID){
        return $this->createQueryBuilder('j')
            ->select('j.id, j.jobTitle, j.industry, j.companyName, j.companyAddress, j.addressCountry, j.addressState, j.addressZipCode,'.
                'j.addressCity, j.addressSuburb, j.addressStreet, j.addressStreetNumber, j.addressBuildName, j.addressUnit, j.companyDescription, j.companyDescriptionChange,'.
                'j.roleDescription, j.roleDescriptionChange, j.closureDate, j.gender, j.ethnicity,'.
                'j.availability, j.location, j.salaryRange, j.approve, j.status, j.created as createdDate, j.started, j.spec'
            )
            ->where('j.id = :jobID')
            ->setParameter('jobID', $jobID)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param ProfileDetails $profileDetails
     * @param array $params
     * @return mixed
     */
    public function getJobsForCandidate(ProfileDetails $profileDetails, $params = array()){
        $query = $this->createQueryBuilder('j')
            ->select('j.id, j.jobTitle, j.industry, j.roleDescriptionChange as roleDescription, j.companyAddress, j.addressCity, j.closureDate as endDate, j.started as createdDate')
            ->where('j.approve = :approve')
            ->setParameter('approve', true)
            ->andWhere('j.status = :status')
            ->setParameter('status', true)

            ->andWhere('((j.gender = :gender1) OR (j.gender = :gender2))')
            ->setParameter('gender1', 'All')
            ->setParameter('gender2', $profileDetails->getGender())

            ->andWhere('((j.ethnicity = :ethnicity1) OR (j.ethnicity = :ethnicity2) OR (j.ethnicity LIKE :ethnicity3))')
            ->setParameter('ethnicity1', 'All')
            ->setParameter('ethnicity2', serialize(['All']))
            ->setParameter('ethnicity3', "%".$profileDetails->getEthnicity()."%");

//            ->andWhere('((j.nationality = :nationality1) OR (j.nationality = :nationality2))')
//            ->setParameter('nationality1', 0)
//            ->setParameter('nationality2', $profileDetails->getNationality());


        if(isset($params['startDate']) && !empty($params['startDate']) && $params['startDate'] != 'null'){
            $date = ($params['startDate'] instanceof \DateTime) ? $params['startDate'] : new \DateTime($params['startDate']);
            if($date instanceof \DateTime){
                $query->andWhere("DATE_FORMAT(j.created, '%Y-%m-%d') >= :startDate")
                    ->setParameter('startDate', $date->format('Y-m-d'));
            }
        }

        if(isset($params['endDate']) && !empty($params['endDate']) && $params['endDate'] != 'null'){
            $date = ($params['endDate'] instanceof \DateTime) ? $params['endDate'] : new \DateTime($params['endDate']);
            if($date instanceof \DateTime){
                $query->andWhere("DATE_FORMAT(j.created, '%Y-%m-%d') <= :endDate")
                    ->setParameter('endDate', $date->format('Y-m-d'));
            }
        }

        $query->orderBy('j.started', 'DESC');


        return $query->getQuery()->getResult();
    }

    /**
     * @param ProfileDetails $profileDetails
     * @param array $params
     * @return mixed
     */
    public function getExpiredJobsForCandidate(ProfileDetails $profileDetails, $params = array()){
        $query = $this->createQueryBuilder('j')
            ->select('j.id, j.jobTitle, j.industry, j.roleDescriptionChange as roleDescription, j.companyAddress, j.addressCity, j.closureDate as endDate, j.started as createdDate')
            ->where('j.approve = :approve')
            ->setParameter('approve', true)
            ->andWhere('j.status = :status')
            ->setParameter('status', false)


            ->andWhere('((j.gender = :gender1) OR (j.gender = :gender2))')
            ->setParameter('gender1', 'All')
            ->setParameter('gender2', $profileDetails->getGender())

            ->andWhere('((j.ethnicity = :ethnicity1) OR (j.ethnicity = :ethnicity2) OR (j.ethnicity LIKE :ethnicity3))')
            ->setParameter('ethnicity1', 'All')
            ->setParameter('ethnicity2', serialize(['All']))
            ->setParameter('ethnicity3', "%".$profileDetails->getEthnicity()."%");

//            ->andWhere('((j.nationality = :nationality1) OR (j.nationality = :nationality2))')
//            ->setParameter('nationality1', 0)
//            ->setParameter('nationality2', $profileDetails->getNationality());



        if(isset($params['startDate']) && !empty($params['startDate']) && $params['startDate'] != 'null'){
            $date = ($params['startDate'] instanceof \DateTime) ? $params['startDate'] : new \DateTime($params['startDate']);
            if($date instanceof \DateTime){
                $query->andWhere("DATE_FORMAT(j.closureDate, '%Y-%m-%d') >= :startDate")
                    ->setParameter('startDate', $date->format('Y-m-d'));
            }
        }

        if(isset($params['endDate']) && !empty($params['endDate']) && $params['endDate'] != 'null'){
            $date = ($params['endDate'] instanceof \DateTime) ? $params['endDate'] : new \DateTime($params['endDate']);
            if($date instanceof \DateTime){
                $query->andWhere("DATE_FORMAT(j.closureDate, '%Y-%m-%d') <= :endDate")
                    ->setParameter('endDate', $date->format('Y-m-d'));
            }
        }

        $query->andWhere("DATE_FORMAT(j.closureDate, '%Y-%m-%d') > :createDate")
            ->setParameter('createDate', $profileDetails->getUser()->getCreated()->format('Y-m-d'));

        $query->orderBy('j.closureDate', 'DESC');


        return $query->getQuery()->getResult();
    }

    /**
     * @param ProfileDetails $profileDetails
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountExpiredJobsForCandidate(ProfileDetails $profileDetails){
        $query = $this->createQueryBuilder('j')
            ->select('count(j.id) as expiredCount')
            ->where('j.approve = :approve')
            ->setParameter('approve', true)
            ->andWhere('j.status = :status')
            ->setParameter('status', false);

            /*->andWhere('((j.articlesFirm = :art1) OR (j.articlesFirm LIKE :art2))')
            ->setParameter('art1', serialize(['All']))
            ->setParameter('art2', "%".$profileDetails->getArticlesFirm()."%")

            ->andWhere('((j.gender = :gender1) OR (j.gender = :gender2))')
            ->setParameter('gender1', 'All')
            ->setParameter('gender2', $profileDetails->getGender())

            ->andWhere('((j.ethnicity = :ethnicity1) OR (j.ethnicity = :ethnicity2))')
            ->setParameter('ethnicity1', 'All')
            ->setParameter('ethnicity2', $profileDetails->getEthnicity())

            ->andWhere('((j.nationality = :nationality1) OR (j.nationality = :nationality2))')
            ->setParameter('nationality1', 0)
            ->setParameter('nationality2', $profileDetails->getNationality()) */

            /*->andWhere('((j.location = :location1) OR (j.location = :location2))')
            ->setParameter('location1', 'All')
            ->setParameter('location2', $profileDetails->getCitiesWorking())*/

            /*->andWhere('((j.qualification = :qualification1) OR (j.qualification = :qualification2))')
            ->setParameter('qualification1', 0);*/

        $query->andWhere("DATE_FORMAT(j.closureDate, '%Y-%m-%d') > :createDate")
            ->setParameter('createDate', $profileDetails->getUser()->getCreated()->format('Y-m-d'));


        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @param ProfileDetails $profileDetails
     * @return int
     */
    public function getCountJobsForCandidate(ProfileDetails $profileDetails){
        $query = $this->createQueryBuilder('j')
            ->select('COUNT(j.id) as countJob')
            ->where('j.approve = :approve')
            ->setParameter('approve', true)
            ->andWhere('j.status = :status')
            ->setParameter('status', true)

            ->andWhere('((j.articlesFirm = :art1) OR (j.articlesFirm LIKE :art2))')
            ->setParameter('art1', serialize(['All']))
            ->setParameter('art2', "%".$profileDetails->getArticlesFirm()."%")

            ->andWhere('((j.gender = :gender1) OR (j.gender = :gender2))')
            ->setParameter('gender1', 'All')
            ->setParameter('gender2', $profileDetails->getGender())

            ->andWhere('((j.ethnicity = :ethnicity1) OR (j.ethnicity = :ethnicity2))')
            ->setParameter('ethnicity1', 'All')
            ->setParameter('ethnicity2', $profileDetails->getEthnicity())

            ->andWhere('((j.nationality = :nationality1) OR (j.nationality = :nationality2))')
            ->setParameter('nationality1', 0)
            ->setParameter('nationality2', $profileDetails->getNationality())

            ->andWhere('((j.qualification = :qualification1) OR (j.qualification = :qualification2))')
            ->setParameter('qualification1', 0);

        if(in_array($profileDetails->getBoards(),[1,2])){
            $query->setParameter('qualification2', 1);
        }
        else{
            $query->setParameter('qualification2', 2);
        }

        $result = $query->getQuery()->getResult();

        return (isset($result[0]['countJob']) && intval($result[0]['countJob']) > 0) ? intval($result[0]['countJob']) : 0;
    }

    /**
     * @param $jobID
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getJobByIdForCandidate($jobID){
        return $this->createQueryBuilder('j')
            ->select('j.id, j.jobTitle, j.industry, j.companyAddress, j.companyDescriptionChange as companyDescription,'.
                'j.roleDescriptionChange as roleDescription, j.closureDate as endDate, j.created as createdDate'
            )
            ->where('j.id = :jobID')
            ->setParameter('jobID', $jobID)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $day
     * @return mixed
     */
    public function getExpirationJobs($day){
        $now = new \DateTime();
        return $this->createQueryBuilder('j')
            ->select("j")
            ->where('j.status = :status')
            ->setParameter('status', true)
            ->andWhere("DATE_DIFF(DATE_FORMAT(j.closureDate,'%Y-%m-%d'), :now) = :day")
            ->setParameter('now', $now->format('Y-m-d'))
            ->setParameter('day', $day)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed
     */
    public function getExpiredJobs(){
        $now = new \DateTime();
        return $this->createQueryBuilder('j')
            ->select("j")
            ->where('j.status = :status')
            ->setParameter('status', true)
            ->andWhere("DATE_DIFF(DATE_FORMAT(j.closureDate,'%Y-%m-%d'), :now) <= 0")
            ->setParameter('now', $now->format('Y-m-d'))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getClosureJobs(){
        $now = new \DateTime();
        return $this->createQueryBuilder('j')
            ->select("j")
            ->where('j.status = :true')
            ->andWhere('j.approve = :true')
            ->setParameter('true', true)
            ->andWhere("(DATE_DIFF(DATE_FORMAT(j.jobClosureDate,'%Y-%m-%d'), :now) = 3 OR DATE_DIFF(DATE_FORMAT(j.jobClosureDate,'%Y-%m-%d'), :now) = 7)")
            ->setParameter('now', $now->format('Y-m-d'))
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $params
     * @return array
     */
    public function getJobSpecApprove($params=array()){
        $query = $this->createQueryBuilder('j')
            ->select('j')
            ->where("j.spec IS NOT NULL");

        $result = $query->getQuery()->getResult();
        $files = [];
        if(!empty($result)){
            foreach ($result as $job) {
                if($job instanceof Job){
                    if(!empty($job->getSpec()) && is_array($job->getSpec())){
                        $file = $job->getSpec();
                        if(isset($file['approved']) && $file['approved'] == false){
                            $check = true;
                            if(isset($params['search']) && !empty($params['search'])){
                                $check = false;
                                if(strpos($job->getUser()->getFirstName(), $params['search']) !== false
                                    || strpos($job->getUser()->getLastName(), $params['search']) !== false
                                    || strpos($file['name'], $params['search']) !== false
                                ){
                                    $check = true;
                                }
                            }
                            if($check == true){
                                $files[] = [
                                    'jobId' => $job->getId(),
                                    'jobTitle' => $job->getJobTitle(),
                                    'userId' => $job->getUser()->getId(),
                                    'firstName' => $job->getUser()->getFirstName(),
                                    'lastName' => $job->getUser()->getLastName(),
                                    'url' => $file['url'],
                                    'adminUrl' => (isset($file['adminUrl'])) ? $file['adminUrl'] : null,
                                    'fileName' => $file['name'],
                                    'time'=>(isset($file['time'])) ? $file['time'] : null
                                ];
                            }
                        }
                    }
                }
            }
        }
        if(isset($params['orderBy']) && !empty($params['orderBy']) && in_array($params['orderBy'], ['Name', 'Document'])){
            if(isset($params['orderSort']) && !empty($params['orderSort']) && in_array($params['orderSort'], ['asc', 'desc'])){
                if($params['orderBy'] == 'Name'){
                    if($params['orderSort'] == 'asc'){
                        usort($files, function($a, $b) {
                            return $a['firstName'] > $b['firstName'];
                        });
                    }
                    else{
                        usort($files, function($a, $b) {
                            return $a['firstName'] < $b['firstName'];
                        });
                    }
                }
                elseif ($params['orderBy'] == 'Document'){
                    if($params['orderSort'] == 'asc'){
                        usort($files, function($a, $b) {
                            return $a['fileName'] > $b['fileName'];
                        });
                    }
                    else{
                        usort($files, function($a, $b) {
                            return $a['fileName'] < $b['fileName'];
                        });
                    }
                }
            }
            else{
                usort($files, function($a, $b) {
                    return $a['time'] < $b['time'];
                });
            }
        }
        else{
            usort($files, function($a, $b) {
                return $a['time'] < $b['time'];
            });
        }

        if(isset($params['lm']) && $params['lm'] > 0){
            $chunkFiles = array_chunk($files, $params['lm']);
            $files = (isset($chunkFiles[0])) ? $chunkFiles[0] : [];
        }

        return $files;
    }
}